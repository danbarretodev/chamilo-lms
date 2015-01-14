<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Solicitud recibida para el curso {{ session.title }}</title>
    <style type="text/css">
        .titulo {
            color: #93c5cd;
            font-family: "Times New Roman", Times, serif;
            font-size: 24px;
            font-weight: bold;
            border-bottom-width: 2px;
            border-bottom-style: solid;
            border-bottom-color: #93c5cd;
         }
    </style>
</head>

<body>
<table width="700" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td><img src="img/header.png" width="700" height="20" alt=""></td>
    </tr>
    <tr>
        <td><img src="img/line.png" width="700" height="25" alt=""></td>
    </tr>
    <tr>
        <td valign="top"><table width="700" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="50">&nbsp;</td>
                    <td width="394"><img src="img/logo-minedu.png" width="230" height="60" alt="Ministerio de Educación"></td>
                    <td width="50">&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td style="color: #93c5cd; font-family: Times New Roman, Times, serif; font-size: 24px; font-weight: bold; border-bottom-width: 2px; border-bottom-style: solid; border-bottom-color: #93c5cd;">Solicitud de consideración de curso para un colaborador</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td height="356">&nbsp;</td>
                    <td valign="top"><p>Estimado:</p>
                        <h2>{{ superior.name }}</h2>
                        <p>Hemos recibido una solicitud de inscripción de <strong>{{ student.name }}</strong> al curso <strong>{{ session.title }}</strong>, por iniciarse el <strong>{{ session.start_date }}</strong>. Detalles del curso: <strong>{{ sesion.description }}</strong>. </p>
                        <p>Le invitamos a aprobar o desarprobar esta inscripción, dando clic en el botón correspondiente a continuación.</p>
                        <table width="100%" border="0" cellspacing="3" cellpadding="4" style="background:#EDE9EA">
                            <tr>
                                <td width="58" valign="middle"><img src="img/avatar.png" width="50" height="50" alt=""></td>
                                <td width="211" valign="middle"><h4>{{ student.name }}</h4></td>
                                <td width="90" valign="middle"><a href="#"><img src="img/aprobar.png" width="90" height="25" alt=""></a></td>
                                <td width="243" valign="middle"><a href="#"><img src="img/desaprobar.png" width="90" height="25" alt=""></a></td>
                            </tr>
                        </table>
                        <p>Gracias.</p>
                        <p><strong>Equipo Forge</strong></p></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td width="50">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td width="50">&nbsp;</td>
                </tr>
            </table></td>
    </tr>
    <tr>
        <td><img src="img/line.png" width="700" height="25" alt=""></td>
    </tr>
    <tr>
        <td><img src="img/footer.png" width="700" height="20" alt=""></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
    </tr>
</table>
</body>
</html>
