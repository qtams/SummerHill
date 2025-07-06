<?php
session_start(); // Start session to access user data

// Redirect to login if session does not exist
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: index.php"); // Redirect to login page
    exit;
}

// Fetch user details from session
$user_id = $_SESSION['user_id'];
$role = htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8');
$username = htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); // Default to empty string if not set
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainees</title>
    <?php include "styles/styles.php" ?>
    <style>
        @keyframes scaleIn {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .school-card {
            transform-origin: center;
            transition: transform 0.3s ease, opacity 0.3s ease, filter 0.3s ease;
        }

        .animate-in {
            animation: scaleIn 0.3s ease-in-out;
        }

        .school-card:hover img {
            transform: scale(1.1);
        }
    </style>
</head>

<body x-data="{
        traineeList: [],
        searchQuery: '',
        filterStatus: 'all',

        init() {
            // Fetch initial data
            this.getTrainee();
            setInterval(() => {
                this.getTrainee();
            }, 3000);  
        },

        getTrainee() {
            fetch('get-table-data.php?get_data=trainee_id')
                .then(response => response.json())
                .then(data => {
                    this.traineeList = data
                        .map(trainee => ({
                            ...trainee,
                            profile: trainee.profile ? trainee.profile : 'images/School/Logo-Placeholder.png'
                        }))
                        .sort((a, b) => (a.status === 'Inactive') - (b.status === 'Inactive'));
                })
                .catch(error => console.error('Error fetching trainee:', error));
        },

        filteredTrainee() {
            return this.traineeList
                .filter(trainee => {
                    const fullName = `${trainee.lastname} ${trainee.firstname}`.toLowerCase();
                    return fullName.includes(this.searchQuery.toLowerCase());
                })
                .filter(trainee => this.filterStatus === 'all' || trainee.status === this.filterStatus);
        },

        formatHours(decimalHours, showMinutes = true) {
            if (decimalHours === null || decimalHours === undefined) return '0 hrs';
            
            const hours = Math.floor(decimalHours);
            const minutes = showMinutes ? Math.round((decimalHours % 1) * 60) : 0;
            
            let result = '';
            
            if (hours > 0) {
                result += hours === 1 ? `${hours} hr` : `${hours} hrs`;
            }
            
            if (showMinutes && minutes >= 0) {
                if (hours > 0) result += ' and ';
                if (minutes === 1) {
                    result += `${minutes} min`;
                } else if (minutes === 0) {
                    result += `${minutes} min`;
                } else {
                    result += `${minutes} mins`;
                }
            }
            
            if (result === '') {
                return showMinutes ? '0 hrs' : '0 hr';
            }
            
            return result;
        }
    }" x-init="getTrainee()" x-cloak>

    <div>
        <div class="flex h-screen w-full" x-data="{ open: true }" x-bind:class="modalAdd ? 'blur-sm' : ''">
            <?php include 'templates/sidebar.php'; ?>

            <div class="w-full duration-500" x-bind:class="open ? 'ml-64' : 'ml-24'">
                <?php include 'templates/header.php'; ?>

                <div class="flex flex-col w-full px-10">
                    <div class="flex-col mt-10">

                        <!-- Search & Filter -->
                        <div class="flex gap-4 mb-4">
                            <div class="relative flex items-center w-1/3">
                                <input type="text" id="traineeSearch" x-model="searchQuery"
                                    class="border shadow-lg py-2 px-8 rounded-full border focus:border-dashColor outline-none w-full"
                                    placeholder="Search trainees...">
                                <i class="fa-solid fa-magnifying-glass absolute top-1/2 -translate-y-1/2 left-2 text-slate-400 text-sm"></i>
                            </div>

                            <select x-model="filterStatus"
                                class="border py-2 px-4 focus:border-dashColor outline-none rounded-md">
                                <option value="all">All</option>
                                <option value="Evaluating">Evaluating</option>
                                <option value="Complete">Complete</option>
                                <option value="Passed">Passed</option>
                                <option value="Failed">Failed</option>
                                <option value="On Going">On Going</option>
                            </select>
                        </div>

                        <!-- Trainee Cards -->
                        <div class="grid grid-cols-3 gap-4 mt-5">
                            <template x-for="trainee in filteredTrainee()" :key="trainee.trainee_id">
                                <div
                                    :id="`trainee-${trainee.trainee_id}`"
                                    :class="{
                                        'school-card': true,
                                        'school-card-initial': trainee.status !== 'Inactive',
                                        'opacity-50 grayscale': trainee.is_hidden === '1'
                                    }"
                                    class="flex bg-modifGray shadow-tableShadow py-10 flex-col gap-4 p-4 rounded-lg transition-all duration-300 transform hover:scale-110 cursor-pointer animate-in">

                                    <!-- Centered Profile Picture -->
                                    <div class="flex justify-center items-center">
                                        <img :src="trainee.profile" alt="Profile Picture"
                                            class="w-40 h-40 object-cover "
                                            :class="'opacity-50 grayscale': trainee.is_hidden === '1'"
                                            loading="lazy">
                                    </div>

                                    <!-- Trainee Info -->
                                    <div class="text-black flex flex-col gap-2">
                                        <span class="text-center text-lg font-bold" x-text="`${trainee.lastname} ${trainee.firstname}`"></span>

                                        <div class="flex gap-2">
                                            <span class="font-bold text-sm">Total Time:</span>
                                            <span x-text="formatHours(trainee.total_hours)"></span>
                                        </div>
                                        <div class="flex gap-2">
                                            <span class="font-bold text-sm">Remaining Time:</span>
                                            <span x-text="formatHours(trainee.remaining_hours)"></span>
                                        </div>
                                        <div class="flex gap-2">
                                            <span class="font-bold text-sm">Time Set:</span>
                                            <span class="text-sm" x-text="formatHours(trainee.set_hours, false)"></span>
                                        </div>
                                        <div class="flex gap-2 items-center justify-center mt-2">
                                            <span class="font-semibold" 
                                                x-text="trainee.status" 
                                                :class="{
                                                    'text-green-600 bg-green-200 rounded-full py-1 px-2': trainee.status === 'Complete',
                                                    'text-orange-600 bg-orange-200 rounded-full py-1 px-2': trainee.status === 'Passed',
                                                    'text-red-600 bg-red-200 rounded-full py-1 px-2': trainee.status === 'Failed',
                                                    'text-blue-600 bg-blue-200 rounded-full py-1 px-2': trainee.status === 'On Going',
                                                    'text-gray-600 bg-gray-200 rounded-full py-1 px-2': trainee.status === 'Evaluating'
                                                }">
                                            </span>
                                        </div>




                                    </div>


                                </div>
                            </template>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const autoLogoutTime = 5 * 60 * 1000;
        let inactivityTimer;

        function resetTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                window.location.href = 'databases/log-out.php';
            }, autoLogoutTime);
        }

        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetTimer);
        });

        resetTimer();
    </script>
</body>

</html>