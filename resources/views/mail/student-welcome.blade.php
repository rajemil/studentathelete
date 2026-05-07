<x-mail::message>
# Welcome, {{ $user->name }}

Your student account has been created.

**Email:** {{ $user->email }}

**Access code (6 characters):** `{{ $plainAccessCode }}`

Use this code with your email the first time you sign in, then change your password from your profile if you like.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
