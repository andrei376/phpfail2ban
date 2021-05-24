<?php

namespace App\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {email : valid email used for login} {password : password for user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $email = $this->argument('email');
            $password = $this->argument('password');

            $validator = Validator::make(['email' => $email, 'password' => $password], [
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ])->validate();

            // dump($email);
            // dump($password);

            $user = User::create([
                'name' => $email,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            event(new Registered($user));

            $this->info("User created.");
        } catch (ValidationException $e) {
            $this->error("Validation Error: ".$e->getMessage());
            $this->error(print_r($e->errors(), true));
        } catch (Exception $e) {
            $this->error("Exception: ".$e->getMessage());
        }

        return 0;
    }
}
