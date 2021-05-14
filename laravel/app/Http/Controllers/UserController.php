<?php

namespace App\Http\Controllers;

use App\Models\User;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     * @noinspection PhpUnused
     */
    public function index(): Response
    {
        //
        $users = User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
                'VerifiedEmailClass' => $user->hasVerifiedEmail() ? 'text-green-700' : 'text-red-700',
                'VerifiedEmailText' => $user->hasVerifiedEmail() ? __('Email verified') : __('Email not verified')
            ];
        });

        return Inertia::render('User/Index', [
            'status' => session('status'),
            'users' => $users
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Inertia\Response
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     * @noinspection PhpUnused
     */
    public function edit(User $user): Response
    {
        //dump($user);

        return Inertia::render('User/Edit', [
            'user' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     * @noinspection PhpUnused
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        //
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'name' => 'required|string|max:255',
            'password' => [
                'nullable',
                'string',
                'confirmed',
                'min:8'
            ]
        ]);

        //
        //dd($request);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if (isset($request->password) && !empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        $sendEmail = false;

        if ($request->email !== $user->email) {
            $user->forceFill([
                'email_verified_at' => null,
            ])->update();

            $sendEmail = true;
        }

        $user->update($data);

        if ($sendEmail) {
            event(new Registered($user));
        }

        session()->flash('msg.success', __('User successfully edited.'));

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @noinspection PhpFullyQualifiedNameUsageInspection
     * @noinspection PhpUnused
     */
    public function destroy(int $user)
    {
        //
        try {
            //$user=1234;

            User::findOrFail($user)->delete();

            session()->flash('msg.warning', __('User successfully deleted.'));

            return Redirect::back();
            //
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " failed to delete user id=".$user."\n"
            );

            return response()->json(['errors' => ''], 422, ['X-Inertia' => true]);
        }
    }
}
