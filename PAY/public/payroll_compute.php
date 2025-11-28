<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Compute</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>

    </style>

</head>

<body>
    <script>
        $.ajax({
            url: "../modules/compute_payroll.php",
            type: "POST",
            dataType: "json",
            success: (response) => {
                if (response.status == 'success') {
                    if (!response.objects.empty) {
                        alert(JSON.stringify(response.objects));
                    }
                }
            }
        });
    </script>
</body>

</html>