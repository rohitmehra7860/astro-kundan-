@if(!empty($faqs))
<section class="faq-section section-pad"><div class="container faq-layout"><div><p class="eyebrow">A FEW ANSWERS</p><h2>Questions you may have</h2><p class="section-intro">If you still have a question, you’re welcome to get in touch.</p></div><div class="faq-list">@foreach($faqs as $faq)<details class="faq-item"><summary>{{ $faq['question'] }}</summary><p>{{ $faq['answer'] }}</p></details>@endforeach</div></div></section>
@endif
