<x-mail::message>
# Welcome aboard, {{ $user->name }}! 👋

We are excited to have you as part of **{{ $user->organization?->name ?? config('app.name') }}**. Your student athlete account has been successfully created and is ready for use.

Below you will find your secure login credentials. Please ensure you keep these details private.

<x-mail::panel>
### Your Login Credentials
**Email:** `{{ $user->email }}`  
**Access Code:** `{{ $plainAccessCode }}`
</x-mail::panel>

<x-mail::button :url="route('login')">
Login to Your Account
</x-mail::button>

### Getting Started is Easy:
1. Click the button above to go to the login page.
2. Enter your registered email address.
3. Use the **6-character access code** provided above as your temporary password.
4. Once logged in, you can complete your profile and explore your dashboard.

> **Security Note:** Your access code is case-sensitive. For your security, please do not share this code with anyone.

If you have any questions or need assistance, please don't hesitate to reach out to your administrator.

Best regards,  
**{{ $user->organization?->name ?? config('app.name') }} Team**
</x-mail::message>
