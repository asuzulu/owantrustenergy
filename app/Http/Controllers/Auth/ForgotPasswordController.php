<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails {
        // alias the trait’s method so we can call it after our custom validation
        sendResetLinkEmail as protected traitSendResetLinkEmail;
    }

    /**
     * Override the default sendResetLinkEmail to first ensure
     * the email exists in the users table.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(
            ['email' => 'required|email|exists:users,email'],
            ['email.exists' => "We can't find a user with that email address."]
        );

        // now call the original trait method to send the link
        return $this->traitSendResetLinkEmail($request);
    }
}
