<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class AstroKundanContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first() ?? User::first();

        if (!$admin) {
            return;
        }

        if (in_array(Setting::get('site_name'), [null, '', 'Website backend', 'Laravel'], true)) {
            Setting::set('site_name', 'Astro Kundan');
        }

        $serviceCategory = Category::firstOrCreate(
            ['slug' => 'astrology-guidance'],
            [
                'name' => 'Astrology Guidance',
                'description' => 'Personal astrology and spiritual guidance consultations.',
                'type' => 'service',
            ]
        );

        $services = [
            [
                'title' => 'Love & Relationship Guidance',
                'slug' => 'love-relationship-guidance',
                'description' => 'A private astrology consultation for questions about love, communication, and relationship choices.',
                'content' => '<p>Bring the relationship questions that matter to you to a personal astrology consultation. We will discuss your concerns through a traditional astrology lens and consider practical next steps at your pace.</p><p>Astrology guidance is for reflection and does not guarantee a particular relationship outcome.</p>',
                'is_featured' => true,
                'faqs' => [
                    ['question' => 'What can I discuss in a relationship consultation?', 'answer' => 'You can share questions about communication, compatibility, or relationship direction. Share only the details you are comfortable providing.'],
                    ['question' => 'Can astrology guarantee that a relationship will work out?', 'answer' => 'No. A consultation offers perspective and guidance, but it cannot guarantee another person’s choices or a specific outcome.'],
                ],
            ],
            [
                'title' => 'Marriage & Compatibility',
                'slug' => 'marriage-compatibility',
                'description' => 'Discuss compatibility, marriage questions, and relationship expectations in a personal consultation.',
                'content' => '<p>A marriage and compatibility consultation gives you space to discuss questions about a relationship, expectations, and traditional astrology perspectives on compatibility.</p><p>Any guidance is intended to support thoughtful, respectful decisions. It is not a substitute for personal choice or professional advice where needed.</p>',
                'is_featured' => true,
                'faqs' => [
                    ['question' => 'What is covered in a marriage compatibility consultation?', 'answer' => 'The discussion can cover your questions about compatibility and marriage expectations through a traditional astrology perspective.'],
                    ['question' => 'Do I need to share birth details when I enquire?', 'answer' => 'No. The website enquiry form does not ask for birth details. If they are useful for a consultation, you can decide what to share later.'],
                ],
            ],
            [
                'title' => 'Kundli Matching',
                'slug' => 'kundli-matching',
                'description' => 'Explore traditional birth-chart compatibility questions in a personal kundli matching consultation.',
                'content' => '<p>Kundli matching is a traditional astrology practice used to discuss compatibility. A consultation can help you understand the questions you have about the process and its interpretation.</p><p>Any interpretation is guidance for reflection, not a guarantee of a marriage outcome.</p>',
                'is_featured' => true,
                'faqs' => [
                    ['question' => 'What information is used for kundli matching?', 'answer' => 'Traditional kundli matching may use birth date, time, and place. You do not need to enter those details on the website enquiry form.'],
                    ['question' => 'Does kundli matching predict a certain future?', 'answer' => 'No. It is a traditional interpretive practice and should not be treated as a certain prediction or a replacement for personal judgment.'],
                ],
            ],
            [
                'title' => 'Personal Horoscope Consultation',
                'slug' => 'personal-horoscope-consultation',
                'description' => 'A one-to-one conversation about your horoscope questions and personal areas of focus.',
                'content' => '<p>Use a personal horoscope consultation to discuss the astrology questions on your mind. The session is shaped around the topics you choose to bring.</p><p>Astrology is an interpretive tradition; guidance is not a guarantee of future events.</p>',
                'is_featured' => false,
                'faqs' => [
                    ['question' => 'Can I ask about more than one topic?', 'answer' => 'You can mention the topics you would like to discuss when you enquire so the consultation can be planned around your questions.'],
                    ['question' => 'Are horoscope readings guaranteed predictions?', 'answer' => 'No. A horoscope consultation provides an interpretive perspective and cannot guarantee future events.'],
                ],
            ],
            [
                'title' => 'Vashikaran & Relationship Guidance',
                'slug' => 'vashikaran-guidance',
                'description' => 'Discuss relationship concerns through a traditional astrology perspective, with guidance focused on respectful and consensual choices.',
                'content' => '<p>This consultation is for discussing personal and relationship concerns through a traditional astrology perspective. Guidance focuses on your own reflection, communication, and choices.</p><p>We do not promise control over another person or guaranteed results. Any guidance should respect everyone’s consent and autonomy.</p>',
                'is_featured' => false,
                'faqs' => [
                    ['question' => 'Does this service guarantee influence over another person?', 'answer' => 'No. We do not promise control over another person or outcomes that override their choices.'],
                    ['question' => 'What is the focus of this consultation?', 'answer' => 'The focus is your concerns and respectful choices within your own relationships and circumstances.'],
                ],
            ],
            [
                'title' => 'Negative Energy & Black Magic Concerns',
                'slug' => 'negative-energy-guidance',
                'description' => 'A calm, non-judgmental consultation for people with spiritual or negative-energy concerns.',
                'content' => '<p>If you have spiritual or negative-energy concerns, you can discuss them in a calm, private consultation. The conversation focuses on understanding your concerns and considering thoughtful next steps.</p><p>No diagnosis or guaranteed result is promised. If you feel unsafe or distressed, seek support from someone you trust or an appropriate professional.</p>',
                'is_featured' => true,
                'faqs' => [
                    ['question' => 'Can a consultation prove that black magic has affected me?', 'answer' => 'No. A consultation cannot establish that as a fact or guarantee a supernatural diagnosis. It can provide space to discuss your concerns and consider next steps.'],
                    ['question' => 'Will I be pressured to buy a ritual or remedy?', 'answer' => 'No. The website does not promise guaranteed outcomes or require you to purchase a ritual or remedy.'],
                ],
            ],
        ];

        foreach ($services as $data) {
            $faqs = $data['faqs'];
            unset($data['faqs']);

            $service = Service::firstOrCreate(
                ['slug' => $data['slug']],
                $data + [
                    'category_id' => $serviceCategory->id,
                    'author_id' => $admin->id,
                    'status' => 'published',
                    'is_pinned' => false,
                ]
            );

            $service->seoMetadata()->firstOrCreate([], [
                'meta_title' => $service->title . ' | Astro Kundan',
                'meta_description' => $service->description,
                'robots_instructions' => 'index, follow',
                'faqs' => $faqs,
            ]);
        }

        $journalCategory = Category::firstOrCreate(
            ['slug' => 'astrology-journal'],
            ['name' => 'Astrology Journal', 'description' => 'General articles about astrology, relationships, and consultation questions.', 'type' => 'blog']
        );

        $posts = [
            [
                'title' => 'What to Expect from a Personal Astrology Consultation',
                'slug' => 'what-to-expect-astrology-consultation',
                'content' => '<p>A personal astrology consultation begins with the questions you choose to bring. You might be thinking about a relationship, a transition, or a decision that has been on your mind. It is fine to begin with a simple question.</p><h2>A conversation at your pace</h2><p>Your astrologer can explain how a traditional astrology perspective relates to your question. You can ask for clarification, share only the details you are comfortable sharing, and decide what feels useful to you.</p><h2>Guidance for reflection</h2><p>Astrology is an interpretive tradition. A consultation can offer a perspective to consider, but it cannot guarantee future events or make choices for you. You remain the person best placed to decide what next step fits your life.</p>',
                'faqs' => [
                    ['question' => 'Do I need to prepare before an astrology consultation?', 'answer' => 'It can help to think about the questions you want to discuss. You do not need to prepare a long explanation; begin with what feels most important.'],
                    ['question' => 'Does an astrology consultation guarantee an outcome?', 'answer' => 'No. Astrology is interpretive guidance and cannot guarantee a specific future event or another person’s choices.'],
                ],
            ],
            [
                'title' => 'Kundli Matching: A Traditional Compatibility Conversation',
                'slug' => 'kundli-matching-compatibility-conversation',
                'content' => '<p>Kundli matching is a traditional practice some people use when discussing marriage compatibility. A consultation can help explain the ideas behind a matching and make room for the questions you have about its interpretation.</p><h2>Keep the discussion in perspective</h2><p>Compatibility is shaped by many parts of a relationship, including communication, shared values, and the way people treat one another. A kundli interpretation should not replace those conversations or the people involved making their own choices.</p><h2>Ask what you need to ask</h2><p>If you are considering a consultation, you can ask how birth details are used and what an interpretation can and cannot tell you. Share personal information only when you are comfortable doing so.</p>',
                'faqs' => [
                    ['question' => 'What details may be used for kundli matching?', 'answer' => 'Traditional kundli matching may use birth date, time, and place. Ask the astrologer how the details will be used before sharing them.'],
                    ['question' => 'Can kundli matching decide whether two people should marry?', 'answer' => 'No. It is an interpretive tradition and should not make the decision for the people involved or replace their own judgment.'],
                ],
            ],
            [
                'title' => 'Making Space for Thoughtful Relationship Choices',
                'slug' => 'thoughtful-relationship-choices',
                'content' => '<p>When a relationship feels uncertain, it can be helpful to slow down and name the questions you are carrying. What do you need to understand? What matters to you? Which conversations have you been putting off?</p><h2>Start with what is in your control</h2><p>You can reflect on your needs, communicate clearly, and decide what boundaries support your wellbeing. No spiritual practice can make choices for another person or replace mutual consent.</p><h2>Seek the support that fits</h2><p>Astrology may be one reflective perspective. Trusted people and qualified professionals can offer other kinds of support. Choose what feels grounded and respectful of everyone involved.</p>',
                'faqs' => [
                    ['question' => 'Can astrology control another person’s feelings?', 'answer' => 'No. Astro Kundan does not promise control over another person. Relationship guidance should respect each person’s consent and autonomy.'],
                    ['question' => 'What can I do if a relationship concern is affecting my wellbeing?', 'answer' => 'Consider speaking with someone you trust or an appropriate qualified professional. Astrology guidance is not a substitute for mental health or safety support.'],
                ],
            ],
        ];

        foreach ($posts as $data) {
            $faqs = $data['faqs'];
            unset($data['faqs']);
            $post = Post::firstOrCreate(['slug' => $data['slug']], $data + [
                'category_id' => $journalCategory->id,
                'author_id' => $admin->id,
                'status' => 'published',
                'is_featured' => false,
                'is_pinned' => false,
            ]);
            $post->seoMetadata()->firstOrCreate([], [
                'meta_title' => $post->title . ' | Astro Kundan Journal',
                'meta_description' => \Illuminate\Support\Str::limit(strip_tags($post->content), 155, ''),
                'robots_instructions' => 'index, follow',
                'faqs' => $faqs,
            ]);
        }

        $pages = [
            'home' => [
                'title' => 'Astro Kundan',
                'content' => 'Personal astrology consultations for love, marriage, relationships, and life guidance.',
                'meta_title' => 'Astro Kundan | Love, Marriage & Astrology Guidance',
                'meta_description' => 'Explore personal astrology consultations for relationship, marriage, kundli, and horoscope questions.',
                'faqs' => [
                    ['question' => 'How do I enquire about a consultation?', 'answer' => 'Choose a service and send an enquiry using the contact form. Astro Kundan can follow up using the contact details you provide.'],
                    ['question' => 'Does astrology guarantee a specific result?', 'answer' => 'No. Astrology guidance is interpretive and cannot guarantee relationship, marriage, or future outcomes.'],
                ],
            ],
            'about' => [
                'title' => 'About Astro Kundan',
                'content' => 'Astro Kundan offers personal astrology consultations in a respectful, welcoming setting. Visitors can explore available services and contact the astrologer with questions.',
                'meta_title' => 'About Astro Kundan',
                'meta_description' => 'Learn about Astro Kundan and the personal astrology consultation approach.',
                'faqs' => [
                    ['question' => 'How are consultations approached?', 'answer' => 'Consultations are shaped around the questions you choose to discuss and use an interpretive astrology perspective.'],
                    ['question' => 'Do I have to share personal details on the website?', 'answer' => 'The enquiry form asks for your name, email, and message. Do not include sensitive details unless you choose to.'],
                ],
            ],
            'contact' => [
                'title' => 'Contact Astro Kundan',
                'content' => 'Send a private enquiry about a service or consultation. Share only the information needed for a response.',
                'meta_title' => 'Contact Astro Kundan',
                'meta_description' => 'Contact Astro Kundan to enquire about astrology services and consultations.',
                'faqs' => [
                    ['question' => 'What happens after I send an enquiry?', 'answer' => 'Your message is recorded and routed to the configured contact email. A team member can follow up using the details you provide.'],
                    ['question' => 'What information should I include?', 'answer' => 'Include your name, a way to reply, the service you are interested in, and a short description of your question.'],
                ],
            ],
            'services' => [
                'title' => 'Astrology Services',
                'content' => 'Explore personal astrology consultations for relationships, marriage, kundli matching, horoscopes, and spiritual concerns.',
                'meta_title' => 'Astrology Services | Astro Kundan',
                'meta_description' => 'Browse Astro Kundan astrology services and enquire about a private consultation.',
                'faqs' => [
                    ['question' => 'How do I choose a service?', 'answer' => 'Read the service descriptions and choose the topic closest to your question. You can mention your service of interest in the enquiry form.'],
                    ['question' => 'Can I enquire before deciding?', 'answer' => 'Yes. Send a question through the contact form before deciding whether to book a consultation.'],
                ],
            ],
            'blog' => [
                'title' => 'Astrology Journal',
                'content' => 'Articles and notes about astrology, relationships, compatibility, and common consultation questions.',
                'meta_title' => 'Astrology Journal | Astro Kundan',
                'meta_description' => 'Read Astro Kundan articles about astrology, relationships, compatibility, and horoscopes.',
                'faqs' => [
                    ['question' => 'Are articles a substitute for a personal consultation?', 'answer' => 'No. Articles provide general information and do not offer personalized guidance.'],
                    ['question' => 'Can I ask a question about an article?', 'answer' => 'Yes. Use the contact page and mention the article or topic you are asking about.'],
                ],
            ],
        ];

        foreach ($pages as $slug => $data) {
            $faqs = $data['faqs'];
            unset($data['faqs']);
            $metaTitle = $data['meta_title'];
            $metaDescription = $data['meta_description'];
            unset($data['meta_title'], $data['meta_description']);
            $pageData = $data + ['status' => 'published', 'is_system' => true];

            $page = Page::firstOrCreate(['slug' => $slug], $pageData);

            // Replace only the untouched placeholder text from SampleDataSeeder.
            if (in_array($page->content, [
                'Welcome to our homepage.',
                'Learn more about our company.',
                'Get in touch with us.',
                'Browse our blog categories.',
                'Browse our service categories.',
                'Read our latest posts and announcements.',
                'Discover our premium services.',
            ], true)) {
                $page->update($pageData);
            }

            $seo = $page->seoMetadata()->firstOrCreate([], [
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'robots_instructions' => 'index, follow',
                'faqs' => $faqs,
            ]);

            if (str_contains((string) $seo->meta_title, 'SEO Title')) {
                $seo->update([
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDescription,
                    'faqs' => $faqs,
                ]);
            }
        }

        // Keep the repository's generic demo article out of the live blog.
        \App\Models\Post::where('slug', 'first-post')
            ->where('title', 'First Post')
            ->update(['status' => 'draft']);
    }
}
