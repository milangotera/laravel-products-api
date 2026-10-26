<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateApiToken extends Command
{
    protected $signature = 'api:token {email}';

    protected $description = 'Crea un token de acceso para utilizar la API';

    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));

        $validator = Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email', 'max:255']]
        );

        if ($validator->fails()) {
            $this->error('Introduce un correo electrónico válido.');

            return self::FAILURE;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Usuario API',
                'password' => Hash::make(Str::random(64)),
            ]
        );

        $token = $user->createToken(
            'Acceso API',
            ['*'],
            now()->addDay()
        );

        $this->info('Token válido durante 24 horas. Guárdalo en un lugar seguro.');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
