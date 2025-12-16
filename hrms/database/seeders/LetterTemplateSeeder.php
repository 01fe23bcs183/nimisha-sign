<?php

namespace Database\Seeders;

use App\Models\ExperienceCertificate;
use App\Models\JoiningLetter;
use App\Models\NocCertificate;
use App\Models\User;
use Illuminate\Database\Seeder;

class LetterTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hrms.com')->first();
        $authorId = $admin?->id ?? 1;

        JoiningLetter::firstOrCreate(
            ['lang' => 'en'],
            [
                'content' => $this->getJoiningLetterContent(),
                'author_id' => $authorId,
            ]
        );

        ExperienceCertificate::firstOrCreate(
            ['lang' => 'en'],
            [
                'content' => $this->getExperienceCertificateContent(),
                'author_id' => $authorId,
            ]
        );

        NocCertificate::firstOrCreate(
            ['lang' => 'en'],
            [
                'content' => $this->getNocCertificateContent(),
                'author_id' => $authorId,
            ]
        );
    }

    private function getJoiningLetterContent(): string
    {
        return <<<'EOT'
<h2 style="text-align: center;">JOINING LETTER</h2>

<p>Date: {date}</p>

<p>Dear {employee_name},</p>

<p>We are pleased to confirm your appointment as <strong>{designation}</strong> in the <strong>{department}</strong> department at our <strong>{branch}</strong> office.</p>

<p>Your employment will commence from <strong>{join_date}</strong>.</p>

<p>Your initial compensation will be <strong>{salary}</strong> per month, subject to applicable taxes and deductions.</p>

<p>Please report to the HR department on your joining date with the following documents:</p>
<ul>
    <li>Original educational certificates for verification</li>
    <li>Government-issued photo ID</li>
    <li>Bank account details</li>
    <li>Two passport-size photographs</li>
</ul>

<p>We look forward to welcoming you to our team and wish you a successful career with us.</p>

<p>Sincerely,</p>
<p><strong>Human Resources Department</strong></p>
EOT;
    }

    private function getExperienceCertificateContent(): string
    {
        return <<<'EOT'
<h2 style="text-align: center;">EXPERIENCE CERTIFICATE</h2>

<p>Date: {date}</p>

<p><strong>To Whom It May Concern</strong></p>

<p>This is to certify that <strong>{employee_name}</strong> was employed with our organization from <strong>{join_date}</strong> to <strong>{exit_date}</strong>.</p>

<p>During their tenure, they held the position of <strong>{designation}</strong> in the <strong>{department}</strong> department at our <strong>{branch}</strong> office.</p>

<p>During their employment, they demonstrated professionalism, dedication, and competence in their assigned responsibilities. Their conduct and performance were satisfactory.</p>

<p>We wish them all the best in their future endeavors.</p>

<p>This certificate is issued upon request for whatever purpose it may serve.</p>

<p>Sincerely,</p>
<p><strong>Human Resources Department</strong></p>
EOT;
    }

    private function getNocCertificateContent(): string
    {
        return <<<'EOT'
<h2 style="text-align: center;">NO OBJECTION CERTIFICATE</h2>

<p>Date: {date}</p>

<p><strong>To Whom It May Concern</strong></p>

<p>This is to certify that <strong>{employee_name}</strong>, currently employed as <strong>{designation}</strong> in the <strong>{department}</strong> department at our <strong>{branch}</strong> office, has requested a No Objection Certificate.</p>

<p>We have no objection to the above-named employee pursuing the stated purpose. This certificate is issued at the request of the employee and does not constitute a release from their current employment obligations.</p>

<p>The employee remains bound by the terms and conditions of their employment contract until formally released.</p>

<p>This certificate is valid for a period of 30 days from the date of issue.</p>

<p>Sincerely,</p>
<p><strong>Human Resources Department</strong></p>
EOT;
    }
}
