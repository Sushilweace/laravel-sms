<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card - {{ $enrollment->student->admission_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .school-name { font-size: 20px; font-weight: bold; text-transform: uppercase; color: #0f172a; margin: 0; }
        .school-sub { font-size: 10px; color: #64748b; margin-top: 4px; }
        .title-badge { display: inline-block; background: #0f172a; color: #fff; padding: 4px 15px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-top: 10px; border-radius: 4px; }
        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 6px; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 10px; }
        .meta-label { color: #64748b; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .meta-val { font-weight: bold; color: #0f172a; font-size: 11px; margin-top: 2px; }
        .scores-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .scores-table th { background: #0f172a; color: #fff; padding: 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        .scores-table td { padding: 7px 8px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-left: 1px solid #e2e8f0; }
        .scores-table tfoot td { background: #f1f5f9; font-weight: bold; border-top: 2px solid #0f172a; }
        .center { text-align: center; }
        .summary-box { width: 100%; border-collapse: collapse; margin-bottom: 30px; text-align: center; }
        .summary-box td { border: 1px solid #cbd5e1; padding: 10px; background: #f8fafc; }
        .summary-num { font-size: 16px; font-weight: bold; color: #0f172a; margin-top: 4px; }
        .sig-table { width: 100%; margin-top: 40px; }
        .sig-table td { width: 33%; text-align: center; font-size: 10px; font-weight: bold; color: #334155; }
        .sig-line { border-top: 1px solid #94a3b8; margin-top: 40px; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="school-name">{{ $school->name ?? 'Greenwood International School' }}</h1>
        <div class="school-sub">{{ $school->address ?? 'Knowledge City, Bangalore' }} | Phone: {{ $school->phone ?? '+91 98765 43210' }}</div>
        <div class="title-badge">Official Progress Report — {{ $exam->name }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td width="25%">
                <div class="meta-label">Student Name</div>
                <div class="meta-val">{{ $enrollment->student->full_name }}</div>
            </td>
            <td width="25%">
                <div class="meta-label">Admission / Roll</div>
                <div class="meta-val">{{ $enrollment->student->admission_no }} (Roll #{{ $enrollment->roll_number ?? '1' }})</div>
            </td>
            <td width="25%">
                <div class="meta-label">Class & Section</div>
                <div class="meta-val">{{ $enrollment->section->full_name }}</div>
            </td>
            <td width="25%">
                <div class="meta-label">Academic Year</div>
                <div class="meta-val">{{ $enrollment->academicYear->name }}</div>
            </td>
        </tr>
    </table>

    <table class="scores-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th class="center" width="70">Max</th>
                <th class="center" width="70">Pass</th>
                <th class="center" width="80">Score</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($marks as $m)
            <tr>
                <td><strong>{{ $m->examSubject->subject->name }}</strong></td>
                <td class="center">{{ number_format($m->examSubject->max_marks) }}</td>
                <td class="center">{{ number_format($m->examSubject->pass_marks) }}</td>
                <td class="center">
                    <strong>{{ $m->is_absent ? 'ABSENT' : $m->effective_marks }}</strong>
                </td>
                <td style="color: #64748b; font-style: italic;">{{ $m->teacher_remarks ?? 'Good performance' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>GRAND TOTAL</td>
                <td class="center">{{ number_format($result->max_marks) }}</td>
                <td class="center">—</td>
                <td class="center" style="color: #4338ca;">{{ $result->total_marks }}</td>
                <td>Overall: <strong>{{ $result->percentage }}%</strong></td>
            </tr>
        </tfoot>
    </table>

    <table class="summary-box">
        <tr>
            <td width="25%">
                <div class="meta-label">Final Percentage</div>
                <div class="summary-num">{{ $result->percentage }}%</div>
            </td>
            <td width="25%">
                <div class="meta-label">Grade</div>
                <div class="summary-num" style="color: #059669;">{{ $result->grade }}</div>
            </td>
            <td width="25%">
                <div class="meta-label">Class Rank</div>
                <div class="summary-num" style="color: #4f46e5;">#{{ $result->class_rank ?? '1' }}</div>
            </td>
            <td width="25%">
                <div class="meta-label">Result</div>
                <div class="summary-num" style="color: {{ $result->result_status === 'pass' ? '#059669' : '#dc2626' }}; text-transform: uppercase;">
                    {{ $result->result_status }}
                </div>
            </td>
        </tr>
    </table>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">Class Teacher</div>
            </td>
            <td>
                <div class="sig-line">Examination Cell</div>
            </td>
            <td>
                <div class="sig-line">Principal</div>
            </td>
        </tr>
    </table>
</body>
</html>
