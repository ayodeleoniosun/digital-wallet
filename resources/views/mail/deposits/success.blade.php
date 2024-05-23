<x-mail::message>
    Hello {{ $deposit->user->fullname }},

    Your account has been credited with {{ number_format($deposit->amount,2) }}.

    Thanks,
    {{ config('app.name') }}
</x-mail::message>
