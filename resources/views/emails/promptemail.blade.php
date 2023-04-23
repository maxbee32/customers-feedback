
@component('mail::message')
# Client's Feedback

Hello branch manager {{$details->email}}.
We received these feedback today on {{$details->date}} from your {{$details->branch}} branch.



| Client's feedback for the day        |
| -------------------------------------- |
| Negative feedbacks {{$details->no}}.   |
| Positive feedbacks {{$details->yes}}.  |
| Total feedbacks {{$details->comment}}. |




Lets work harder for clients to appreciate our hardwork.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
