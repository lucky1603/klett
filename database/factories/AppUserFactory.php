<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AppUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ime' => $this->faker->firstName(),
            'prezime' => $this->faker->lastName(),
            'email' => $this->faker->unique()->email(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'adresa' => $this->faker->streetAddress(),
            'pb' => $this->faker->postcode(),
            'mesto' => $this->faker->city(),
            'country_id' => 1,
            'tel1' => $this->faker->phoneNumber(),
            'tel2' => $this->faker->phoneNumber(),
            'is_teacher' => 0
        ];
    }
}
