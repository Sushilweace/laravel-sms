<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Subject Marksheet - {{ $examSubject->subject->name }} - {{ $exam->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .school-name {
            font-size: 16pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
        }
        .school-info {
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
        }
        .sheet-title {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 14px;
            background: #0f172a;
            color: #ffffff;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px;
            font-size: 8.5pt;
            margin-bottom: 12px;
        }
        .meta-table td {
            padding: 3px 6px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .table th, .table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            text-align: center;
        }
        .table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .text-left { text-align: left !important; }
        .bold { font-weight: bold; }
        .pass { color: #15803d; font-weight: bold; }
        .fail { color: #b91c1c; font-weight: bold; }
        .signature-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .signature-table td {
            text-align: center;
            padding-top: 25px;
            border-top: 1px dashed #64748b;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="school-name">{{ $school->name ?? 'Greenwood International School' }}</h1>
        <div class="school-info">
            {{ $school->address ?? 'Knowledge City, Bangalore' }} • Phone: {{ $school->phone ?? '+91 98765 43210' }}
        </div>
        <div class="sheet-title">Subject Marks Award Register</div>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 33%;"><strong>Subject:</strong> {{ $examSubject->subject->name }} ({{ $examSubject->subject->code }})</td>
            <td style="width: 33%;"><strong>Class & Section:</strong> {{ $section->full_name }}</td>
            <td style="width: 34%; text-align: right;"><strong>Examination:</strong> {{ $exam->name }}</td>
        </tr>
        <tr>
            <td><strong>Maximum Marks:</strong> {{ (int)$examSubject->max_marks }}</td>
            <td><strong>Passing Marks:</strong> {{ (int)($examSubject->pass_marks ?? 33) }}</td>
            <td style="text-align: right;"><strong>Date Generated:</strong> {{ now()->format('d M Y') }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 35px;">Roll</th>
                <th style="width: 80px;">Adm No</th>
                <th class="text-left">Student Full Name</th>
                <th style="width: 60px;">Max Marks</th>
                <th style="width: 75px;">Marks Scored</th>
                <th style="width: 60px;">Grace</th>
                <th style="width: 75px;">Final Score</th>
                <th style="width: 50px;">Status</th>
                <th class="text-left" style="width: 120px;">Faculty Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $enrollment)
                @php
                    $m = $marks[$enrollment->id] ?? null;
                    $isPass = $m && !$m->is_absent && (float)$m->final_marks >= ($examSubject->pass_marks ?? 33);
                @endphp
                <tr>
                    <td class="bold">{{ $enrollment->roll_number ?? '—' }}</td>
                    <td style="font-family: monospace;">{{ $enrollment->student->admission_no }}</td>
                    <td class="text-left bold">{{ $enrollment->student->full_name }}</td>
                    <td>{{ (int)$examSubject->max_marks }}</td>
                    <td class="bold">
                        @if(!$m)
                            <span style="color: #94a3b8;">—</span>
                        @elseif($m->is_absent)
                            <span style="color: #dc2626; font-weight: bold;">AB (Absent)</span>
                        @else
                            {{ (float)$m->marks_obtained }}
                        @endif
                    </td>
                    <td>{{ $m && (float)$m->grace_marks > 0 ? (float)$m->grace_marks : '0' }}</td>
                    <td class="bold">
                        @if(!$m)
                            <span style="color: #94a3b8;">—</span>
                        @elseif($m->is_absent)
                            <span style="color: #dc2626;">0.00</span>
                        @else
                            <span class="{{ $isPass ? 'pass' : 'fail' }}">{{ (float)$m->final_marks }}</span>
                        @endif
                    </td>
                    <td>
                        @if(!$m)
                            <span style="color: #94a3b8;">—</span>
                        @elseif($m->is_absent)
                            <span class="fail">ABSENT</span>
                        @else
                            <span class="{{ $isPass ? 'pass' : 'fail' }}">{{ $isPass ? 'PASS' : 'FAIL' }}</span>
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 7.5pt; color: #475569;">
                        {{ $m->remarks ?? '—' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 33%;">
                <strong>Subject Faculty Signature</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Evaluator</span>
            </td>
            <td style="width: 33%;">
                <strong>HOD / Moderator Signature</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Department Verification</span>
            </td>
            <td style="width: 34%;">
                <strong>Exam Controller Signature</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Official Approval</span>
            </td>
        </tr>
    </table>

</body>
</html>
