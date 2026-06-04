<x-mail::message>
# Welcome, {{ $user->name }}

Your student account has been created for **{{ config('app.name') }}**.

**Email:** {{ $user->email }}

Please verify your email address using the link we sent separately, then sign in with the password you chose (or the password set by your administrator).

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
