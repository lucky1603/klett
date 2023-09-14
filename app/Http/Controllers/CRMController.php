<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CRMController extends Controller
{
    public function connectCRM() {
        return Http::asForm()
            ->post('https://login.microsoftonline.com/570b0e1b-60ff-4adf-8b73-5a3dab04aa93/oauth2/v2.0/token', [
            "grant_type" => 'Client_Credentials',
            "client_id" => '2f9027fe-9597-46bc-818b-d7af10d52016',
            'client_secret' => '5tJ8Q~3ZSQgSb1aGN8e2rv7opFqUdkhKgmOwbbWH',
            'scope' => 'https://klf.crm4.dynamics.com/.default',
        ]);
    }

    public function checkUser($userEmail) {
        $response = $this->connectCRM();
        $token = $response->json('access_token');


        $select = "contactid,ext_cmslogin,emailaddress1,firstname,lastname,address1_line1,ext_postanskibroj,_ext_opstina_value,_ext_grad_value,_ext_drzava_value,mobilephone,telephone1,_ext_funkcijatip_value";
        $expand = "ext_Predmetprofila_Nastavnik_Contact(\$select=ext_klfprocenat,ext_korisnik,ext_poslednjipreracunkorisnika,_ext_predmet_value,ext_razred,_ext_skola_value)";
        $filter = "(emailaddress1 eq '".$userEmail."' and (parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a754452c-b664-ec11-8f8f-6045bd888602 or parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a654452c-b664-ec11-8f8f-6045bd888602 or parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a954452c-b664-ec11-8f8f-6045bd888602))";

        $requestUrl = "https://klf.crm4.dynamics.com/api/data/v9.2/contacts";
        $requestUrl .= "?\$select=".$select;
        $requestUrl .= "&\$expand=".$expand;
        $requestUrl .= "&\$filter=".$filter;

        // var_dump($requestUrl);

        $response = Http::withToken($token)
            ->get($requestUrl);
        return $response->json("value");
    }

    public function getOpstine() {
        $response = $this->connectCRM();
        $token = $response->json('access_token');

        $requestUrl = 'https://klf.crm4.dynamics.com/api/data/v9.2/ext_opstinas?$select=ext_naziv';
        $response = Http::withToken($token)->get($requestUrl);

        // return $response->json('value');
        $opstineRaw = $response->json('value');
        $results = [];
        foreach($opstineRaw as $opstinaRaw) {
            $results[] = [
                'value' => $opstinaRaw['ext_opstinaid'],
                'text' => $opstinaRaw['ext_naziv']
            ];
        }

        return $results;
    }

    public function getPredmeti() {
        $response = $this->connectCRM();
        $token = $response->json('access_token');

        $requestUrl = 'https://klf.crm4.dynamics.com/api/data/v9.2/ext_predmets?$select=ext_predmetid,ext_naziv,ext_cirilicninaziv';
        $response = Http::withToken($token)->get($requestUrl);

        $predmetiRaw = $response->json('value');
        $results = [];
        foreach($predmetiRaw as $predmetRaw) {
            $results[] = [
                'value' => $predmetRaw['ext_predmetid'],
                'text' => $predmetRaw['ext_naziv']
            ];
        }

        return $results;
    }

    public function getTipoviKontakata() {
        $response = $this->connectCRM();
        $token = $response->json('access_token');

        $requestUrl = 'https://klf.crm4.dynamics.com/api/data/v9.2/ext_tipposlovnogkontaktas?$select=ext_naziv';
        $response = Http::withToken($token)->get($requestUrl);

        // return $response->json('value');
        $kontakti = $response->json('value');
        // return $kontakti;
        $results = [];
        foreach($kontakti as $kontakt) {
            if(!in_array($kontakt['ext_naziv'], ['Kupac', 'Dobavljač', 'Distributer', 'Institucija'])) {

                $results[] = [
                    'value' => $kontakt['ext_tipposlovnogkontaktaid'],
                    'text' => $kontakt['ext_naziv']
                ];
            }

        }

        return $results;
    }

    public function getSkole(Request $request) {
        $response = $this->connectCRM();
        $token = $response->json('access_token');
        $data = $request->post();

        $opstinaId = $data['opstina'] ?? null;
        $tipSkole = $data['tipSkole'] ?? null;

        // $opstinaId = 'c5aab172-b864-ec11-8f8f-6045bd888602';
        // $tipSkole = 'a654452c-b664-ec11-8f8f-6045bd888602';


        $requestUrl = 'https://klf.crm4.dynamics.com/api/data/v9.2/accounts?$select=name,_ext_opstina_value,_ext_tipposlovnogkontakta_value,ext_cirilicninazivposlovnogkontakta';

        if($opstinaId != null && $tipSkole != null) {
            $requestUrl = $requestUrl . "&\$filter=_ext_tipposlovnogkontakta_value eq '".$tipSkole."' and statecode eq 0 and _ext_opstina_value eq '".$opstinaId."'";
        }

        $response = Http::withToken($token)->get($requestUrl);
        $skole = $response->json('value');

        $results = [];
        if($skole != null) {
            foreach($skole as $skola) {
                $results[] = [
                    'text' => $skola["name"],
                    'value' => $skola["accountid"]

                ];
            }
        }


        return $results;
    }
}
