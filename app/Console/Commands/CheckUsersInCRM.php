<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CheckUsersInCRM extends Command
{
    protected $signature = 'crm:check-users
                            {--reset : Počni od početka, ignoriši checkpoint}
                            {--batch=50 : Broj korisnika po batch-u}
                            {--delay=200 : Pauza između CRM poziva u milisekundama}';

    protected $description = 'Prolazi kroz sve Keycloak korisnike i poziva checkUser za svaki';

    private const PROGRESS_FILE = 'crm_check_progress.json';
    private const CRM_TOKEN_URL = 'https://login.microsoftonline.com/570b0e1b-60ff-4adf-8b73-5a3dab04aa93/oauth2/v2.0/token';
    private const CRM_API_URL   = 'https://klf.crm4.dynamics.com/api/data/v9.2/contacts';

    private string $crmToken      = '';
    private int    $crmTokenExpiry = 0;
    private string $kcToken       = '';
    private int    $kcTokenExpiry  = 0;

    public function handle(): int
    {
        $batchSize = (int) $this->option('batch');
        $delayMs   = (int) $this->option('delay');

        // Učitaj ili resetuj checkpoint
        if ($this->option('reset') || !Storage::exists(self::PROGRESS_FILE)) {
            $progress = [
                'offset'    => 0,
                'processed' => 0,
                'found'     => 0,
                'not_found' => 0,
                'errors'    => 0,
            ];
            Storage::put(self::PROGRESS_FILE, json_encode($progress));
            $this->info('Checkpoint resetovan, krećemo od početka.');
        } else {
            $progress = json_decode(Storage::get(self::PROGRESS_FILE), true);
            $this->info("Nastavljam od pozicije {$progress['offset']} (već obrađeno: {$progress['processed']}).");
        }

        // Inicijalno uzimanje tokena
        $this->refreshKcToken();
        $this->refreshCrmToken();

        // Ukupan broj korisnika u Keycloak-u
        $total = Http::withToken($this->kcToken)
            ->get(env('KEYCLOAK_API_USERS_URL') . 'count')
            ->json();

        if (!is_int($total)) {
            $this->error('Nije moguće dobiti broj korisnika iz Keycloak-a.');
            return self::FAILURE;
        }

        $this->info("Ukupno korisnika u Keycloak-u: {$total}");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->setProgress($progress['offset']);

        while ($progress['offset'] < $total) {
            // Osvežavaj tokene na vreme
            if (time() > $this->kcTokenExpiry - 30) {
                $this->refreshKcToken();
            }
            if (time() > $this->crmTokenExpiry - 60) {
                $this->refreshCrmToken();
            }

            // Uzmi batch korisnika iz Keycloak-a
            $response = Http::withToken($this->kcToken)
                ->get(env('KEYCLOAK_API_USERS_URL'), [
                    'briefRepresentation' => 'true',
                    'first' => $progress['offset'],
                    'max'   => $batchSize,
                ]);

            if (!$response->successful()) {
                $this->error("Keycloak API greška: HTTP {$response->status()}");
                break;
            }

            $users = $response->json();

            if (empty($users) || !is_array($users)) {
                break;
            }

            foreach ($users as $user) {
                $email = $user['email'] ?? null;

                if (!$email) {
                    $progress['offset']++;
                    $progress['processed']++;
                    $bar->advance();
                    continue;
                }

                try {
                    $result = $this->checkUser($email);

                    if (!empty($result)) {
                        $progress['found']++;
                        Log::channel('crm_sync')->info('Pronađen u CRM', [
                            'email'     => $email,
                            'contactid' => $result[0]['contactid'] ?? null,
                        ]);
                    } else {
                        $progress['not_found']++;
                        Log::channel('crm_sync')->info('Nije pronađen u CRM', [
                            'email' => $email,
                        ]);
                    }
                } catch (\Throwable $e) {
                    $progress['errors']++;
                    Log::channel('crm_sync')->error('Greška', [
                        'email' => $email,
                        'error' => $e->getMessage(),
                    ]);
                }

                $progress['processed']++;
                $progress['offset']++;
                $bar->advance();

                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }
            }

            // Sačuvaj checkpoint posle svakog batch-a
            Storage::put(self::PROGRESS_FILE, json_encode($progress));
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Obrađeno', 'Pronađeno u CRM', 'Nije pronađeno', 'Greške'],
            [[$progress['processed'], $progress['found'], $progress['not_found'], $progress['errors']]]
        );

        if ($progress['offset'] >= $total) {
            Storage::delete(self::PROGRESS_FILE);
            $this->info('Sve obrađeno. Checkpoint obrisan.');
        } else {
            $this->warn("Prekinuto na poziciji {$progress['offset']}. Pokrenite ponovo da nastavite.");
        }

        return self::SUCCESS;
    }

    private function checkUser(string $email): ?array
    {
        $select = implode(',', [
            'contactid', 'ext_cmslogin', 'emailaddress1', 'firstname', 'lastname',
            'address1_line1', 'ext_postanskibroj', '_ext_opstina_value', '_ext_grad_value',
            '_ext_drzava_value', 'mobilephone', 'telephone1', '_ext_funkcijatip_value',
        ]);

        $expand = "ext_Predmetprofila_Nastavnik_Contact("
            . "\$select=ext_klfprocenat,ext_korisnik,ext_poslednjipreracunkorisnika,_ext_predmet_value,ext_razred,_ext_skola_value"
            . ";\$filter=statecode eq 0"
            . ";\$expand=ext_Predmet(\$select=ext_naziv,ext_cirilicninaziv)"
            . ",ext_Skola(\$select=ext_crmid,ext_cirilicninazivposlovnogkontakta,_ext_saradnik_value,ext_saradniknaziv;\$filter=statecode eq 0)"
            . ")";

        $filter = "(emailaddress1 eq '" . addslashes($email) . "'"
            . " and (parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a754452c-b664-ec11-8f8f-6045bd888602"
            . " or parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a654452c-b664-ec11-8f8f-6045bd888602"
            . " or parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a954452c-b664-ec11-8f8f-6045bd888602))";

        $url = self::CRM_API_URL
            . '?$select=' . $select
            . '&$expand=' . $expand
            . '&$filter=' . $filter;

        $response = Http::withToken($this->crmToken)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("CRM API greška: HTTP {$response->status()}");
        }

        return $response->json('value');
    }

    private function refreshKcToken(): void
    {
        $response = Http::asForm()->post(env('KEYCLOAK_TOKEN_URL'), [
            'client_id'  => 'admin-cli',
            'username'   => 'admin',
            'password'   => env('KEYCLOAK_AUTH_PASSWORD'),
            'grant_type' => 'password',
        ]);

        $this->kcToken      = $response->json('access_token');
        $expiresIn          = $response->json('expires_in') ?? 60;
        $this->kcTokenExpiry = time() + $expiresIn;
    }

    private function refreshCrmToken(): void
    {
        $response = Http::asForm()->post(self::CRM_TOKEN_URL, [
            'grant_type'    => 'Client_Credentials',
            'client_id'     => env('CRM_APP_ID'),
            'client_secret' => env('CRM_SECRET'),
            'scope'         => 'https://klf.crm4.dynamics.com/.default',
        ]);

        $this->crmToken      = $response->json('access_token');
        $expiresIn           = $response->json('expires_in') ?? 3600;
        $this->crmTokenExpiry = time() + $expiresIn;
    }
}
