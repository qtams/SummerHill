
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <?php include "styles/styles.php"; ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none; }</style>
</head>
<body>
<div x-data="{
        open: true,
        modalShow: false,
        modalEditShow: false,
        role: '<?php echo $role; ?>',
        TraineeCount: 0,
        EquipmentsCount: 0,
        ItemsCount: 0,

        init() {
            this.getDashboardCounts();
            setInterval(() => this.getDashboardCounts(), 3000);
        },

        getDashboardCounts() {
            fetch('get-table-data.php?get_data=CountAll')
                .then(response => response.json())
                .then(data => {
                    this.TraineeCount = data.TraineeCount || 0;
                    this.EquipmentsCount = data.EquipmentsCount || 0;
                    this.ItemsCount = data.ItemsCount || 0;
                })
                .catch(error => console.error('Error fetching dashboard counts:', error));
        }
    }"
>
    <div class="flex h-screen w-full" x-data="{ open: true }"x-bind:class="modalShow || modalEditShow ? 'blur-sm' : ''">
        <?php include 'templates/sidebar.php'; ?>
        <div class="w-full duration-500" x-bind:class="open ? 'ml-64' : 'ml-24'" x-cloak>
            <?php include 'templates/header.php'; ?>

            <div class="flex flex-col w-full px-10 mt-10">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
                    <p class="text-lg text-gray-600">
                        You are logged in as a <span class="font-semibold text-orange-500" x-text="role"></span>.
                    </p>
                </div>

              <div class="grid grid-cols-3 gap-5 p-6">
                    <!-- Default Card -->
                    <div class="gap-2 shadow-pressDownDeep flex flex-col w-full p-10 rounded-lg bg-gray-50">
                        <span class="text-3xl font-bold">42</span>
                        <span>Students</span>
                    </div>

                    <!-- Pressed Card (Your Example) -->
                    <div class="gap-2 shadow-pressDownDeep flex flex-col w-full p-10 rounded-lg bg-gray-50">
                        <span class="text-3xl font-bold">128</span>
                        <span>Presents</span>
                    </div>

                    <!-- Stronger Pressed Effect -->
                    <div class="gap-2 shadow-pressDownDeep flex flex-col w-full p-10 rounded-lg bg-gray-50">
                        <span class="text-3xl font-bold">64</span>
                        <span>Absents</span>
                    </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>
