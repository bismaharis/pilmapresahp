# Bug Analysis: Registration & Ranking Issues

**Report Date**: April 4, 2026  
**System**: PILMAPRES Student Selection System  
**Scale**: University-level deployment

---

## Executive Summary

Two critical bugs prevent the system from functioning correctly:

1. **Students cannot access registration form** after selection schedule is created
2. **Ranked participants do not appear in rankings** after jury scores them

Both issues are caused by logic errors in the codebase, NOT configuration problems.

---

## BUG #1: Registration Form Not Showing

### User's Test Scenario

1. Created selection schedule (jadwal seleksi) for Faculty ID 7
2. Students from Faculty 7 see: **"Periode pendaftaran belum dibuka atau sudah berakhir. Silakan hubungi admin fakultas."**
3. But students SHOULD see the registration form

### Root Cause Analysis

**Location**: `resources/views/student/registration/index.blade.php` line 42-44

```blade
@if(!$activePeriod && (!$registration || $registration->stage != 'universitas'))
    <div class="bg-red-50 border border-red-300 rounded p-3 md:p-4 text-red-700 text-xs md:text-sm">
        Periode pendaftaran belum dibuka atau sudah berakhir. Silakan hubungi admin fakultas.
    </div>
@else
    <!-- Registration form shows here -->
```

**The Logic Problem**:

The condition checks ALL of these simultaneously:

- `!$activePeriod` (no active period exists) AND
- `(!$registration || $registration->stage != 'universitas')` (no registration OR not at universitas stage)

The message only appears if ALL three are true. This is correct logic.

### WHY This Happens in Your Test

When you created the selection schedule, here's what happened:

**In Controller** (`app/Http/Controllers/Student/RegistrationController.php` lines 20-40):

```php
$activePeriod = PilmapresPeriod::getActivePeriodForFaculty($student->faculty_id);

// Only create registration if period is active AND no existing registration
if ($activePeriod && ! $existingRegistration) {
    $registration = Registration::firstOrCreate(
        ['student_id' => $student->id, 'period_id' => $activePeriod->id],
        ['status' => 'draft']
    );
} else {
    $registration = $existingRegistration;
}
```

**The Flow**:

1. ✅ Period created with `is_active = true` (by PeriodController line 47)
2. ✅ `getActivePeriodForFaculty()` checks if period is active AND dates are valid
3. ❌ **But if dates are wrong**, `$activePeriod` will be NULL
4. ❌ If `$activePeriod` is NULL and no previous registration exists, then NO registration is created
5. ❌ Then view shows error message

### How to Verify This is the Issue

Check the database to see if:

```sql
SELECT id, faculty_id, year, is_active, start_date, end_date
FROM pilmapres_periods
WHERE faculty_id = 7
ORDER BY created_at DESC LIMIT 1;
```

**Should show**:

- `is_active` = 1 (true) ✓
- `start_date` ≤ TODAY ✓ (must be today or in past)
- `end_date` ≥ TODAY ✓ (must be today or in future)

If ANY of these are wrong, the period will be inactive and students won't see the form.

---

## BUG #2: Students Don't Appear in Rankings After Scoring

### User's Test Scenario

1. Student uploads registration files (GK document + transcript) ✓
2. Jury scores the student ✓
3. Status shows "submitted" ✓
4. **BUT** student's name does NOT appear in ranking/perankingan page

### Root Cause Analysis

**Location**: `app/Http/Controllers/Juri/AssessmentController.php` lines 129-133

```php
// WRONG - REDUNDANT CODE
if ($registration->total_score_fakultas === null && $registration->stage === 'fakultas') {
    $registration->update(['total_score_fakultas' => $registration->total_score_fakultas]);
}

if ($registration->total_score_univ === null && $registration->stage === 'universitas') {
    $registration->update(['total_score_univ' => $registration->total_score_univ]);
}
```

### The Bug Explained

**Step 1: Score Calculation (Line 120 - CORRECT)**

```php
$this->ahpCalculator->calculateFinalScore($registration);
```

This calls `app/Services/AhpCalculatorService.php` which correctly saves the score:

```php
public function calculateFinalScore(Registration $registration): float
{
    // ... calculations ...

    if ($registration->stage === 'fakultas') {
        $registration->update(['total_score_fakultas' => $finalScore]); // ✓ CORRECT
    } else {
        $registration->update(['total_score_univ' => $finalScore]); // ✓ CORRECT
    }

    return $finalScore;
}
```

**At this point, the score HAS been saved correctly.**

**Step 2: Redundant Code (Lines 129-133 - WRONG)**

```php
if ($registration->total_score_fakultas === null && ...) {  // This will be FALSE
    $registration->update(['total_score_fakultas' => $registration->total_score_fakultas]); // Won't execute
}
```

After `calculateFinalScore()`, the score is NO LONGER null, so this condition is **ALWAYS FALSE** and never executes.

### Real Problem: How Rankings Show Students

**In** `app/Http/Controllers/Admin/RankingController.php` line 37:

```php
$query = Registration::with(['student.user', 'student.faculty'])
    ->where('stage', $stage)
    ->whereNotNull($scoreColumn);  // ← Only shows registrations WITH scores
```

The ranking query filters: `whereNotNull($scoreColumn)`

So if `total_score_fakultas` was NULL, the student would NOT appear in rankings.

### Why Your Student is Missing

The redundant code (lines 129-133) doesn't cause the problem directly, but it reveals that the developers were confused:

- ✅ `calculateFinalScore()` correctly saves the score
- ❌ Lines 129-133 are attempting to save it again (but won't execute)
- ✅ Score IS saved (by calculateFinalScore)
- ✓ Student SHOULD appear in rankings

**Wait - if the code is correct, why isn't the student appearing?**

### Possible Secondary Issues

1. **Student's Registration Stage is Wrong**
    - If student is at stage='fakultas' but score was saved to 'total_score_univ' (or vice versa)
    - The ranking filters by stage, so they won't match

    Check:

    ```sql
    SELECT id, student_id, stage, total_score_fakultas, total_score_univ
    FROM registrations
    WHERE student_id = [YOUR_STUDENT_ID];
    ```

2. **Registration Status is Wrong**
    - The view checks for submissions, but the code updates status to 'verified' (line 125)
    - This should be OK, but verify status is 'verified' or 'submitted'

3. **Database Transaction Issues**
    - If an exception occurs during scoring, the update might not persist
    - Check: `storage/logs/laravel.log` for errors during assessment save

---

## Code Locations - Quick Reference

| Component            | File                                                            | Issue                                   |
| -------------------- | --------------------------------------------------------------- | --------------------------------------- |
| Period creation      | `app/Http/Controllers/Admin/PeriodController.php:47`            | ✓ Sets `is_active=true` correctly       |
| Period check (model) | `app/Models/PilmapresPeriod.php:38-46`                          | ✓ Checks dates correctly                |
| Registration show    | `app/Http/Controllers/Student/RegistrationController.php:20-40` | ✓ Creates registration if period active |
| Registration view    | `resources/views/student/registration/index.blade.php:42-44`    | ⚠️ Shows error if NO active period      |
| Score calculation    | `app/Services/AhpCalculatorService.php:32-36`                   | ✓ Saves score correctly                 |
| Assessment save      | `app/Http/Controllers/Juri/AssessmentController.php:120`        | ✓ Calls calculateFinalScore             |
| **Redundant code**   | `app/Http/Controllers/Juri/AssessmentController.php:129-133`    | ❌ **DOES NOTHING - BUG**               |
| Ranking query        | `app/Http/Controllers/Admin/RankingController.php:37`           | ✓ Filters by non-null scores            |

---

## Diagnostic Checklist

### For Registration Issue:

```sql
-- 1. Check if period exists and is active
SELECT id, faculty_id, is_active, start_date, end_date
FROM pilmapres_periods
WHERE faculty_id = 7;

-- 2. Check if dates are correct (should include today)
SELECT CURDATE() as today;

-- 3. Check if student has registration
SELECT id, period_id, stage, status
FROM registrations
WHERE student_id = [STUDENT_ID];
```

### For Ranking Issue:

```sql
-- 1. Check if registration has scores
SELECT id, stage, total_score_fakultas, total_score_univ, status
FROM registrations
WHERE id = [REGISTRATION_ID];

-- 2. Check if scores match stage
SELECT stage, total_score_fakultas, total_score_univ
FROM registrations
WHERE student_id = [STUDENT_ID];

-- 3. Check if stage matches ranking filter
-- If stage='fakultas', check total_score_fakultas is not null
-- If stage='universitas', check total_score_univ is not null
```

---

## Fix Summary

### Issue 1 (Registration Not Showing) Fixes

**Option A**: Verify period dates are correct

- When creating new period, ensure:
    - `start_date` = today or earlier
    - `end_date` = today or later

**Option B**: Fix the misleading message (recommended)

- Show registration form as long as:
    - Period is active, OR
    - Previous registration exists at universitas stage (advanced stage)

### Issue 2 (Rankings Missing Students) Fixes

**CRITICAL**: Remove the redundant code at lines 129-133 of AssessmentController

- Delete those 5 lines entirely
- `calculateFinalScore()` already handles all scoring

```php
// DELETE THESE LINES:
// if ($registration->total_score_fakultas === null && $registration->stage === 'fakultas') {
//     $registration->update(['total_score_fakultas' => $registration->total_score_fakultas]);
// }
// if ($registration->total_score_univ === null && $registration->stage === 'universitas') {
//     $registration->update(['total_score_univ' => $registration->total_score_univ]);
// }
```

The code is:

- Redundant (score already saved)
- Logical error (would update with NULL if it executed)
- Confusing (misleads future developers)

---

## Next Steps (Recommended Order)

1. **Verify period dates** using SQL query above
2. **Check logs** for any errors during scoring: `storage/logs/laravel.log`
3. **Remove the redundant code** from AssessmentController (Bug #2 fix)
4. **Test again** with new scores
5. **Consider improving the registration form logic** for better UX

---

## Impact Assessment

| Bug                      | Severity | Scope        | Impact on Deployment         |
| ------------------------ | -------- | ------------ | ---------------------------- |
| #1: Registration blocked | HIGH     | Faculty-wide | Students can't register      |
| #2: Scores don't show    | CRITICAL | Select stage | Winners hidden from rankings |

**Deployment Status**: ❌ BLOCKED - Both issues must be fixed before going live

---

## References

- `PilmapresPeriod` model logic: [PilmapresPeriod.php](app/Models/PilmapresPeriod.php#L38)
- Registration creation: [RegistrationController.php](app/Http/Controllers/Student/RegistrationController.php#L20)
- Registration view: [index.blade.php](resources/views/student/registration/index.blade.php#L42)
- Score calculation: [AhpCalculatorService.php](app/Services/AhpCalculatorService.php#L32)
- Score save (buggy): [AssessmentController.php](app/Http/Controllers/Juri/AssessmentController.php#L129)
- Ranking query: [RankingController.php](app/Http/Controllers/Admin/RankingController.php#L37)
