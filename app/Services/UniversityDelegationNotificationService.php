<?php

namespace App\Services;

use App\Mail\UniversityDelegationCongratulationsMail;
use App\Models\Lecturer;
use App\Models\Registration;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class UniversityDelegationNotificationService
{
    public function sendParticipantDelegatedToUniversity(Registration $registration): void
    {
        $registration->loadMissing('student.user');

        if (! $registration->student || ! $registration->student->user) {
            return;
        }

        $this->sendEmail(
            $registration->student->user,
            'Peserta Pilmapres'
        );
    }

    public function sendJuryDelegatedToUniversity(Lecturer $lecturer): void
    {
        $lecturer->loadMissing('user');

        if (! $lecturer->user) {
            return;
        }

        $this->sendEmail(
            $lecturer->user,
            'Juri Pilmapres'
        );
    }

    private function sendEmail(User $recipient, string $recipientRoleLabel): void
    {
        $rankings = $this->buildUniversityRankings();
        $pdfBinary = $this->buildUniversityRankingPdf($rankings, $recipientRoleLabel);
        $pdfFileName = 'delegasi-universitas-ranking-'.now()->format('Ymd_His').'.pdf';

        Mail::to($recipient->email)->send(
            new UniversityDelegationCongratulationsMail(
                recipientName: $recipient->name,
                recipientRoleLabel: $recipientRoleLabel,
                pdfBinary: $pdfBinary,
                pdfFileName: $pdfFileName,
            )
        );
    }

    /**
     * @return Collection<int, Registration>
     */
    private function buildUniversityRankings(): Collection
    {
        return Registration::with(['student.user', 'student.faculty'])
            ->where('stage', 'universitas')
            ->orderByDesc('total_score_univ')
            ->orderByDesc('total_score_fakultas')
            ->get();
    }

    /**
     * @param  Collection<int, Registration>  $rankings
     */
    private function buildUniversityRankingPdf(Collection $rankings, string $recipientRoleLabel): string
    {
        $pdf = Pdf::loadView('emails.university-delegation-ranking-pdf', [
            'rankings' => $rankings,
            'recipientRoleLabel' => $recipientRoleLabel,
        ]);

        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
