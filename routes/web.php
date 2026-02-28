<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\CriteriaController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    $role = Auth::user()->role;

    if ($role === 'mahasiswa') {
        return redirect()->route('student.registration.index');
    } elseif ($role === 'dosen') {
        return redirect()->route('juri.assessments.index');
    } elseif (in_array($role, ['super_admin', 'admin_univ', 'admin_fakultas'])) {
        return redirect()->route('admin.ranking.index');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::patch('/profile/academic', [ProfileController::class, 'updateAcademic'])->name('profile.academic.update');
    Route::patch('/profile/lecturer', [ProfileController::class, 'updateLecturer'])->name('profile.lecturer.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/transparency', [\App\Http\Controllers\TransparencyController::class, 'index'])->name('transparency.index');
    Route::get('/transparency/pdf', [\App\Http\Controllers\TransparencyController::class, 'exportPdf'])->name('transparency.pdf');
    Route::get('/transparency/{id}/detail', [\App\Http\Controllers\TransparencyController::class, 'show'])->name('transparency.show');
});

Route::middleware(['auth', 'verified', 'role:super_admin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/committees', [\App\Http\Controllers\SuperAdmin\CommitteeController::class, 'index'])->name('committees.index');
        Route::post('/committees', [\App\Http\Controllers\SuperAdmin\CommitteeController::class, 'store'])->name('committees.store');
        Route::delete('/committees/{user}', [\App\Http\Controllers\SuperAdmin\CommitteeController::class, 'destroy'])->name('committees.destroy');
        Route::get('/committees/{user}/edit', [\App\Http\Controllers\SuperAdmin\CommitteeController::class, 'edit'])->name('committees.edit');
        Route::put('/committees/{user}', [\App\Http\Controllers\SuperAdmin\CommitteeController::class, 'update'])->name('committees.update');

        Route::get('/delegation/juries', [\App\Http\Controllers\SuperAdmin\JuryDelegationController::class, 'index'])->name('delegation.juries.index');
        Route::patch('/delegation/juries/{lecturer}/toggle', [\App\Http\Controllers\SuperAdmin\JuryDelegationController::class, 'toggle'])->name('delegation.juries.toggle');

});

Route::middleware(['auth', 'verified', 'role:super_admin,admin_univ,admin_fakultas'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/criteria', [\App\Http\Controllers\Admin\CriteriaController::class, 'index'])->name('criteria.index');
        Route::post('/criteria', [\App\Http\Controllers\Admin\CriteriaController::class, 'store'])->name('criteria.store');
        Route::put('/criteria/{id}', [\App\Http\Controllers\Admin\CriteriaController::class, 'update'])->name('criteria.update');
        Route::delete('/criteria/{id}', [\App\Http\Controllers\Admin\CriteriaController::class, 'destroy'])->name('criteria.destroy');

        Route::get('/ranking', [\App\Http\Controllers\Admin\RankingController::class, 'index'])->name('ranking.index');
        Route::get('/ranking/pdf', [\App\Http\Controllers\Admin\RankingController::class, 'exportPdf'])->name('ranking.pdf');
        Route::post('/ranking/{registration}/delegate', [\App\Http\Controllers\Admin\RankingController::class, 'delegate'])->name('ranking.delegate');
        Route::patch('/ranking/{registration}/cancel-delegate', [\App\Http\Controllers\Admin\RankingController::class, 'cancelDelegate'])->name('ranking.cancel_delegate');
        
        Route::get('/juries', [\App\Http\Controllers\Admin\JuriController::class, 'index'])->name('juries.index');
        Route::post('/juries', [\App\Http\Controllers\Admin\JuriController::class, 'store'])->name('juries.store');
        Route::delete('/juries/{user}', [\App\Http\Controllers\Admin\JuriController::class, 'destroy'])->name('juries.destroy');
        Route::get('/juries/{user}/edit', [\App\Http\Controllers\Admin\JuriController::class, 'edit'])->name('juries.edit');
        Route::put('/juries/{user}', [\App\Http\Controllers\Admin\JuriController::class, 'update'])->name('juries.update');

        Route::get('/participants', [\App\Http\Controllers\Admin\ParticipantController::class, 'index'])->name('participants.index');
        Route::post('/participants', [\App\Http\Controllers\Admin\ParticipantController::class, 'store'])->name('participants.store');
        Route::put('/participants/{user}', [\App\Http\Controllers\Admin\ParticipantController::class, 'update'])->name('participants.update');
        Route::delete('/participants/{user}', [\App\Http\Controllers\Admin\ParticipantController::class, 'destroy'])->name('participants.destroy');
});

Route::middleware(['auth', 'verified', 'role:dosen'])
    ->prefix('juri')
    ->name('juri.')
    ->group(function () {
        Route::get('/assessments', [\App\Http\Controllers\Juri\AssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/{registration}/edit', [\App\Http\Controllers\Juri\AssessmentController::class, 'edit'])->name('assessments.edit');
        Route::put('/assessments/{registration}', [\App\Http\Controllers\Juri\AssessmentController::class, 'update'])->name('assessments.update');
});

Route::middleware(['auth', 'verified', 'role:mahasiswa'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/registration', [\App\Http\Controllers\Student\RegistrationController::class, 'index'])->name('registration.index');
        Route::put('/registration', [\App\Http\Controllers\Student\RegistrationController::class, 'update'])->name('registration.update');
        
        Route::get('/achievements', [\App\Http\Controllers\Student\AchievementController::class, 'index'])->name('achievements.index');
        Route::post('/achievements', [\App\Http\Controllers\Student\AchievementController::class, 'store'])->name('achievements.store');
        Route::delete('/achievements/{id}', [\App\Http\Controllers\Student\AchievementController::class, 'destroy'])->name('achievements.destroy');
        // Route::get('/transparency', [\App\Http\Controllers\Student\TransparencyController::class, 'index'])->name('transparency.index');
        // Route::get('/transparency/detail', [\App\Http\Controllers\Student\TransparencyController::class, 'show'])->name('transparency.show');
});

Route::get('/force-logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
});

require __DIR__.'/auth.php';
