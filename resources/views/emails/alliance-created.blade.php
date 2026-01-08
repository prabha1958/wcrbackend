<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Alliance Created</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f4f6f8; padding:20px;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="20" cellspacing="0" style="background:#ffffff; border-radius:6px;">
                    <tr>
                        <td align="center" style="background:#1e293b; color:#ffffff; border-radius:6px 6px 0 0;">
                            <h2>CSI Centenary Wesley Church</h2>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <p>Dear {{ $member->first_name ?? 'Member' }},</p>

                            <p>
                                An <strong>Alliance profile</strong> has been successfully created on your behalf by the
                                Church administration.
                            </p>

                            <h3>Alliance Details</h3>

                            <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
                                <tr>
                                    <td><strong>Name</strong></td>
                                    <td>{{ $alliance->first_name }} {{ $alliance->last_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Family Name</strong></td>
                                    <td>{{ $alliance->family_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alliance Type</strong></td>
                                    <td>{{ ucfirst($alliance->alliance_type) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date of Birth</strong></td>
                                    <td>{{ optional($alliance->date_of_birth)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Profession</strong></td>
                                    <td>{{ $alliance->profession ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Designation</strong></td>
                                    <td>{{ $alliance->designation ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>
                                        {{ $alliance->is_published ? 'Published' : 'Pending Approval' }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin-top:15px;">
                                The profile will be reviewed by the administrator and published once approved.
                            </p>

                            <p>
                                If you notice any incorrect information, please contact the Church office.
                            </p>

                            <p>
                                Regards,<br>
                                <strong>CSI Centenary Wesley Church</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="background:#f1f5f9; font-size:12px; color:#555;">
                            © {{ date('Y') }} CSI Centenary Wesley Church. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
