<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'excerpt' => 'Learn how NEXVIA collects, protects, uses, and shares your personal information across our platform and services.',
                'content' => "<h2>Privacy Policy for NEXVIA</h2>
<p>At NEXVIA, accessible from our official website and mobile applications, the privacy of our visitors and customers is of extreme importance. This Privacy Policy document contains types of information that is collected and recorded by NEXVIA and how we use it.</p>
<p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact our Data Protection and Grievance Officer.</p>
<h3>Consent</h3>
<p>By using our website, platform, and applications, you hereby consent to our Privacy Policy and agree to its terms.</p>",
                'points' => [
                    [
                        'title' => 'Information We Collect',
                        'description' => 'We collect personal information that you provide when registering an account, booking products, managing referral wallets, or contacting customer support (e.g. name, phone number, email address, shipping address, identity verification documents, and payment details).'
                    ],
                    [
                        'title' => 'How We Use Your Information',
                        'description' => 'We utilize collected data to operate, maintain, and provide the features of NEXVIA, fulfill product bookings, process 20% advance payments and final settlements, administer referral commissions, schedule doorstep deliveries, and provide customer support.'
                    ],
                    [
                        'title' => 'Data Protection & Security',
                        'description' => 'We deploy industry-standard 256-bit SSL encryption, tokenized authentication, and secure access protocols to safeguard personal information from unauthorized access, alteration, disclosure, or destruction.'
                    ],
                    [
                        'title' => 'Cookies and Tracking Technologies',
                        'description' => 'NEXVIA uses cookies and session tokens to record visitor preferences, track active sessions, optimize website/app performance, and provide tailored product recommendations.'
                    ],
                    [
                        'title' => 'Third-Party Service Providers',
                        'description' => 'We do not sell your personal data. We only share necessary data with trusted third parties strictly to facilitate transactions, such as payment gateways, SMS/OTP notification providers, and verified logistics partners.'
                    ],
                    [
                        'title' => 'User Rights & Data Control',
                        'description' => 'You have the right to inspect, update, or request the deletion of your personal account data at any time through your account dashboard or by contacting our grievance officer.'
                    ]
                ],
                'meta_title' => 'Privacy Policy - NEXVIA',
                'meta_description' => 'Read NEXVIA\'s privacy policy to understand how we protect and manage your personal data and privacy.',
                'meta_keywords' => 'privacy policy, nexvia privacy, data security, user rights',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Terms and Conditions',
                'slug' => 'terms-and-conditions',
                'excerpt' => 'Terms, rules, and guidelines governing user accounts, product bookings, referral credits, and platform services at NEXVIA.',
                'content' => "<h2>Terms and Conditions of Use</h2>
<p>Welcome to NEXVIA. These terms and conditions outline the rules and regulations for the use of NEXVIA's Platform and Mobile Applications.</p>
<p>By accessing or using our platform, placing product bookings, or participating in the Self Dealer Referral program, you accept these terms in full.</p>",
                'points' => [
                    [
                        'title' => 'Account Registration & Eligibility',
                        'description' => 'Users must provide accurate, verified phone numbers and identity credentials. You are responsible for safeguarding your login credentials and one-time passwords (OTPs).'
                    ],
                    [
                        'title' => '20% Booking Engine & 60-Day Settlement',
                        'description' => 'Customers can secure select catalog products by paying a 20% upfront booking deposit. The remaining 80% balance must be settled within the designated 60-day settlement window before product dispatch and doorstep delivery.'
                    ],
                    [
                        'title' => 'Self Dealer Referral Program',
                        'description' => 'Self-dealers earn referral rewards and tier milestones based on verified purchases. Any fraudulent self-referrals, fake accounts, or system abuse will result in commission forfeiture and account suspension.'
                    ],
                    [
                        'title' => 'Doorstep Delivery & Installation',
                        'description' => 'Upon complete payment verification, deliveries and scheduled technician installations are coordinated through certified logistics and service technicians.'
                    ],
                    [
                        'title' => 'Intellectual Property',
                        'description' => 'All trademarks, logos, system software, product designs, and content displayed on NEXVIA are the intellectual property of NEXVIA and protected under applicable laws.'
                    ]
                ],
                'meta_title' => 'Terms & Conditions - NEXVIA',
                'meta_description' => 'Review the official terms and conditions for using NEXVIA and booking products.',
                'meta_keywords' => 'terms and conditions, booking rules, user agreement, nexvia terms',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Refund and Cancellation Policy',
                'slug' => 'refund-policy',
                'excerpt' => 'Details on cancellation periods, refund eligibility for booking deposits, and return guidelines.',
                'content' => "<h2>Refund and Cancellation Policy</h2>
<p>NEXVIA strives to ensure complete satisfaction for all our customers. This policy details how cancellations, returns, and refunds are handled for product bookings and purchases.</p>",
                'points' => [
                    [
                        'title' => 'Booking Cancellation Window',
                        'description' => 'Booking deposits may be cancelled through the mobile application or customer portal prior to final dispatch preparation.'
                    ],
                    [
                        'title' => 'Refund Processing & Timelines',
                        'description' => 'Approved refunds are credited back to the original payment source (bank account / UPI / card) within 5 to 7 business days following cancellation approval.'
                    ],
                    [
                        'title' => 'Damaged or Defective Items',
                        'description' => 'If a product is received damaged or defective upon delivery, report it to our customer support within 48 hours for immediate replacement or full refund under our warranty guarantee.'
                    ],
                    [
                        'title' => 'Non-Refundable Circumstances',
                        'description' => 'Products altered after delivery, items lacking original serial numbers, or cancellations initiated after dispatch handover are subject to standard inspection before any partial refund or credit is issued.'
                    ]
                ],
                'meta_title' => 'Refund & Cancellation Policy - NEXVIA',
                'meta_description' => 'Understand the refund, cancellation, and replacement policy of NEXVIA.',
                'meta_keywords' => 'refund policy, cancellation policy, return policy, nexvia refunds',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'excerpt' => 'Empowering smart consumers with sustainable electric mobility, smart home electronics, and community self-dealer rewards.',
                'content' => "<h2>About NEXVIA</h2>
<p>NEXVIA is a next-generation consumer electronics and green mobility platform redefining how customers discover, finance, and own high-value products.</p>
<p>With an innovative 20% booking engine and an empowered Self Dealer network, NEXVIA bridges the gap between premium technology and affordable ownership.</p>",
                'points' => [
                    [
                        'title' => 'Our Vision',
                        'description' => 'To make premium electric mobility, smart appliances, and clean technology accessible to every household across the nation.'
                    ],
                    [
                        'title' => 'Innovative 20% Booking Model',
                        'description' => 'We pioneered the flexible 20% booking system with a generous 60-day settlement window, letting users plan purchases without stressful credit lock-ins.'
                    ],
                    [
                        'title' => 'Empowering Self Dealers',
                        'description' => 'Our transparent multi-tier referral and dealer ecosystem allows passionate advocates to build recurring income and earn product credits.'
                    ],
                    [
                        'title' => 'Quality & Doorstep Warranty',
                        'description' => 'Every NEXVIA product is backed by certified manufacturer warranties, prompt doorstep servicing, and dedicated installation support.'
                    ]
                ],
                'meta_title' => 'About Us - NEXVIA',
                'meta_description' => 'Discover the mission, vision, and innovative models behind NEXVIA.',
                'meta_keywords' => 'about nexvia, electric mobility, booking engine, company profile',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Contact and Support',
                'slug' => 'contact-us',
                'excerpt' => 'Get in touch with the NEXVIA support team, customer grievance officer, or regional hub centers.',
                'content' => "<h2>Contact & Customer Support</h2>
<p>Have questions about your booking, doorstep delivery, warranty status, or referral credits? Our team is here to assist you 6 days a week.</p>",
                'points' => [
                    [
                        'title' => 'Customer Care Hotline',
                        'description' => 'Toll-free customer helpline: +91 1800-NEXVIA (1800 639 842), Monday through Saturday, 9:00 AM – 7:00 PM IST.'
                    ],
                    [
                        'title' => 'Email Support',
                        'description' => 'For support and order queries: support@nexvia.in | For dealer & business inquiries: partners@nexvia.in'
                    ],
                    [
                        'title' => 'Grievance Officer',
                        'description' => 'In accordance with Information Technology rules, our appointed Grievance Officer can be reached at grievance@nexvia.in.'
                    ],
                    [
                        'title' => 'Corporate Headquarters',
                        'description' => 'NEXVIA Technologies Private Limited, Innovation Hub, Tech Park Boulevard, India.'
                    ]
                ],
                'meta_title' => 'Contact Us - NEXVIA Support',
                'meta_description' => 'Contact the NEXVIA customer care team, technical assistance, or business partnership desk.',
                'meta_keywords' => 'contact nexvia, customer care, support email, phone number',
                'is_active' => true,
                'sort_order' => 5,
            ]
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                $pageData
            );
        }
    }
}
