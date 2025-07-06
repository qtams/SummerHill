<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <?php include "styles/styles.php"; ?>
</head>

<body>
    <div>
        <div class="flex h-screen w-full" x-data="{ open: true }" x-bind:class="modalShow || modalEditShow ? 'blur-sm' : ''">
            <!-- Sidebar -->
            <?php include 'templates/sidebar.php'; ?>

            <!-- Content -->
            <div class="w-full duration-500" x-bind:class="open ? 'ml-64' : 'ml-24'" x-cloak>
                <?php include 'templates/header.php'; ?>
                <div>
                    <div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>