@component('mail::message')
# Congratulations!

You have been successfully registered for the **Ph.D. programme** at
**Rathinam Global Deemed to be University**.

A copy of your registered application will be sent to your registered
email address shortly.

@if(!empty($app->full_name))
**Applicant:** {{ $app->full_name }}
@endif

Thank you for applying.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
