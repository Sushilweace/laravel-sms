<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Consolidated Marksheet - {{ $exam->name }} - {{ $section->full_name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .school-name {
            font-size: 16pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .school-info {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }
        .sheet-title {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 14px;
            background: #0f172a;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 3px;
        }
        .meta-bar {
            width: 100%;
            margin-bottom: 8px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 8pt;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1px 4px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .table th, .table td {
            border: 1px solid #94a3b8;
            padding: 4px 3px;
            text-align: center;
        }
        .table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            font-size: 7pt;
        }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .bold { font-weight: bold; }
        .pass { color: #15803d; font-weight: bold; }
        .fail { color: #b91c1c; font-weight: bold; }
        .sub-name {
            font-size: 6.5pt;
            display: block;
            color: #475569;
        }
        .stats-box {
            margin-top: 8px;
            width: 100%;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 4px 8px;
            font-size: 7.5pt;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats-table td {
            padding: 2px 8px;
        }
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .signature-table td {
            text-align: center;
            padding-top: 20px;
            vertical-align: bottom;
            border-top: 1px dashed #64748b;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1 class="school-name">{{ $school->name ?? 'Greenwood International School' }}</h1>
        <div class="school-info">
            {{ $school->address ?? 'Knowledge City, Bangalore' }} • Phone: {{ $school->phone ?? '+91 98765 43210' }} • Email: {{ $school->email ?? 'contact@greenwood.test' }}
        </div>
        <div class="sheet-title">Consolidated Tabulation Register & Marksheet</div>
    </div>

    <!-- Meta Information -->
    <div class="meta-bar">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;"><strong>Examination:</strong> {{ $exam->name }}</td>
                <td style="width: 25%;"><strong>Class & Section:</strong> {{ $section->full_name }}</td>
                <td style="width: 25%;"><strong>Academic Session:</strong> {{ $exam->academicYear->name }}</td>
                <td style="width: 25%; text-align: right;"><strong>Date Generated:</strong> {{ now()->format('d M Y') }}</td>
            </tr>
            <tr>
                <td><strong>Grading System:</strong> CBSE 9-Point Scale</td>
                <td><strong>Class Teacher:</strong> {{ $section->classTeacher->user->name ?? 'Unassigned' }}</td>
                <td><strong>Total Enrolled:</strong> {{ $enrollments->count() }} Students</td>
                <td style="text-align: right;"><strong>Status:</strong> {{ ucfirst($exam->status) }}</td>
            </tr>
        </table>
    </div>

    <!-- Marksheet Grid -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 25px;">Roll</th>
                <th style="width: 50px;">Adm No</th>
                <th style="width: 130px;" class="text-left">Student Full Name</th>
                @foreach($subjects as $es)
                    <th>
                        {{ $es->subject->code ?? substr($es->subject->name, 0, 4) }}
                        <span class="sub-name">Max: {{ (int)$es->max_marks }}</span>
                    </th>
                @endforeach
                <th style="width: 55px;">Total</th>
                <th style="width: 40px;">%</th>
                <th style="width: 35px;">Grade</th>
                <th style="width: 30px;">GPA</th>
                <th style="width: 40px;">Rank</th>
                <th style="width: 45px;">Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $enrollment)
                @php
                    $res = $results[$enrollment->id] ?? null;
                    $stMarks = $marks[$enrollment->id] ?? collect();
                @endphp
                <tr>
                    <td class="bold">{{ $enrollment->roll_number ?? '—' }}</td>
                    <td style="font-family: monospace;">{{ $enrollment->student->admission_no }}</td>
                    <td class="text-left bold">{{ $enrollment->student->full_name }}</td>
                    
                    @foreach($subjects as $es)
                        @php
                            $m = $stMarks->firstWhere('exam_subject_id', $es->id);
                        @endphp
                        <td>
                            @if(!$m)
                                <span style="color: #94a3b8;">—</span>
                            @elseif($m->is_absent)
                                <span style="color: #dc2626; font-weight: bold;">AB</span>
                            @else
                                <span class="{{ (float)$m->final_marks < ($es->pass_marks ?? 33) ? 'fail' : '' }}">
                                    {{ (float)$m->final_marks }}
                                </span>
                            @endif
                        </td>
                    @endforeach

                    <td class="bold">
                        {{ $res ? (float)$res->total_marks . ' / ' . (int)$res->max_marks : '—' }}
                    </td>
                    <td class="bold">
                        {{ $res ? number_format((float)$res->percentage, 1) . '%' : '—' }}
                    </td>
                    <td class="bold" style="color: #1d4ed8;">
                        {{ $res->grade ?? '—' }}
                    </td>
                    <td style="font-family: monospace;">
                        {{ $res->gpa ?? '—' }}
                    </td>
                    <td class="bold">
                        #{{ $res->section_rank ?? '—' }}
                    </td>
                    <td>
                        @if($res)
                            <span class="{{ $res->result_status === 'pass' ? 'pass' : 'fail' }}">
                                {{ strtoupper($res->result_status) }}
                            </span>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Statistical Summary -->
    <div class="stats-box">
        <table class="stats-table">
            <tr>
                <td><strong>Class Strength:</strong> {{ $stats['total'] }}</td>
                <td><strong>Students Appeared:</strong> {{ $stats['appeared'] }}</td>
                <td><strong>Passed:</strong> <span class="pass">{{ $stats['passed'] }}</span></td>
                <td><strong>Failed / Compartment:</strong> <span class="fail">{{ $stats['failed'] }}</span></td>
                <td><strong>Pass Percentage:</strong> <span class="bold">{{ $stats['pass_percentage'] }}%</span></td>
                <td><strong>Class Average:</strong> <span class="bold">{{ $stats['average_percentage'] }}%</span></td>
                <td><strong>Highest Score:</strong> <span class="pass">{{ $stats['highest_percentage'] }}%</span></td>
                <td><strong>Lowest Score:</strong> <span class="fail">{{ $stats['lowest_percentage'] }}%</span></td>
            </tr>
        </table>
    </div>

    <!-- Official Signatures -->
    <table class="signature-table">
        <tr>
            <td style="width: 25%;">
                <strong>Prepared By</strong><br>
                <span style="font-size: 7pt; color: #64748b;">Subject Faculty / Tabulator</span>
            </td>
            <td style="width: 25%;">
                <strong>Verified By</strong><br>
                <span style="font-size: 7pt; color: #64748b;">Class Teacher</span>
            </td>
            <td style="width: 25%;">
                <strong>Checked By</strong><br>
                <span style="font-size: 7pt; color: #64748b;">Examination In-Charge</span>
            </td>
            <td style="width: 25%;">
                <strong>Approved By</strong><br>
                <span style="font-size: 7pt; color: #64748b;">Principal / Head of Institution</span>
            </td>
        </tr>
    </table>

</body>
</html>
