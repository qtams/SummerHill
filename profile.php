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
    <title>Profile</title>
    <?php include "styles/styles.php"; ?>
</head>

<body x-data="{
    role: '<?php echo $role; ?>',
    user_id: '<?php echo $user_id; ?>',
    username: '<?php echo $username; ?>',
    password: '',
    showPassword: false, // Toggle password visibility
    newPassword: false,
    newPasswords: '',
    confirmPasswords: '',
    newPasswordErrMsg: [], // Initialize error array
    confirmPasswordErrMsg: [], 
    hasError: false, 
  getInformation() {
        fetch(`get-table-data.php?get_data=Information&user_id=${this.user_id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                this.password = data.password || 'No password found';
                this.role = data.role || this.role; // Fallback to initial role if not in response
                this.username = data.username || this.username; // Fallback to initial username
            })
            .catch(error => {
                console.error('Error fetching user information:', error);
                // Set default values if fetch fails
                this.password = 'Error loading password';
            });
    },
    validateForm() {
        this.newPasswordErrMsg = [];
        this.confirmPasswordErrMsg = [];
        this.hasError = false; 
        
        if (this.newPasswords.length > 0) {
            if (!/[A-Z]/.test(this.newPasswords)) {
                this.hasError = true;
                this.newPasswordErrMsg.push('*Password must contain at least 1 uppercase letter.');
            }
            if (!/[0-9]/.test(this.newPasswords)) {
                this.hasError = true;
                this.newPasswordErrMsg.push('*Password must contain at least 1 number.');
            }
            if (!/[!@#$%^&*(),.?:{}|<>_]/.test(this.newPasswords)) {
                this.hasError = true;
                this.newPasswordErrMsg.push('*Password must contain at least 1 special character.');
            }
            if (this.newPasswords.length < 8 || this.newPasswords.length > 16) {
                this.hasError = true;
                this.newPasswordErrMsg.push('*Password should be between 8 to 16 characters only!');
            }
        } else {
            this.hasError = true;
        }

        if (this.confirmPasswords.length > 0) {
            if (this.confirmPasswords !== this.newPasswords) {
                this.hasError = true;
                this.confirmPasswordErrMsg.push('*Password does not match.');
            }
        } else {
            this.hasError = true;
        }
    },
    clearFormErrMsg() {
        this.hasError = false;
        this.newPasswordErrMsg = [];
        this.confirmPasswordErrMsg = [];
    },

    clearFormInput() {
        this.newPasswords = ''; 
        this.confirmPasswords = '';
    },
    submitPasswordForm(event) {
            event.preventDefault();

            this.validateForm();

            if (this.hasError) {
                console.log('Form has errors, submission stopped!');
                return;
            }

            let formData = new FormData(event.target);

            // Debugging: Log the form data before sending
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            if (!formData.has('user_id')) {
                console.error('Error: user_id is missing in formData!');
            }

            fetch('databases/edit-information.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server Response:', data);
                this.alertMessage = data.message;
                this.alertType = data.status;

                if (data.status === 'success') {
                    this.newPassword = false;
                    this.clearFormInput();
                    this.clearFormErrMsg();
                    this.getInformation();
                }

                setTimeout(() => { this.alertMessage = ''; }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                this.alertMessage = 'Something went wrong!';
                this.alertType = 'error';
                setTimeout(() => { this.alertMessage = ''; }, 3000);
            });
        },
}" x-init="getInformation()" x-cloak>

    <div>
        <div class="flex h-screen w-full" x-data="{ open: true }" x-bind:class="modalShow || modalEditShow ? 'blur-sm' : ''">
            <!-- Sidebar -->
            <?php include 'templates/sidebar.php'; ?>

            <!-- Content -->
            <div class="w-full duration-500" x-bind:class="open ? 'ml-64' : 'ml-24'" x-cloak>
                <?php include 'templates/header.php'; ?>
                <div class="flex flex-col w-full px-10 mt-5">
                    <div class="flex flex-col px-10 gap-5">
                        <span class="text-3xl text-gray-600">
                            <span class="font-semibold text-orange-500" x-text="role"></span> Information
                        </span>
                        <div class="w-full flex flex-col max-w-4xl mx-auto bg-white shadow-tableShadow rounded-lg p-6 flex gap-6">
                            <div class="flex">
                                <!-- School Logo & Name Section -->
                                <div class="w-1/2 flex flex-col items-center justify-center text-center p-4">
                                    <img src="images/CCSS.png" alt="CCSS Logo" class="w-60 object-cover rounded-full">
                                    <span class="mt-3 text-xl font-semibold text-gray-800"> CCSS ADMIN</span>
                                </div>

                                <!-- School Info Section -->
                                <div class="w-1/2 bg-gray-50 rounded-lg shadow-tableShadow flex flex-col justify-center px-8 py-10">
                                    <div class="flex flex-col gap-5">
                                        <div class="flex justify-between border-b border-gray-400 pb-2 w-full">
                                            <span class="font-semibold text-gray-700">Username:</span>
                                            <span class="ml-2 text-gray-800" x-text="username"></span>
                                        </div>
                                        <div class="flex justify-between border-b border-gray-400 pb-2 w-full">
                                            <span class="font-semibold text-gray-700">Password:</span>
                                            <div class="flex items-center">
                                                <span class="ml-2 text-gray-800" x-text="showPassword ? password : '********'"></span>
                                                <button @click="showPassword = !showPassword" class="ml-2 text-blue-500 hover:text-blue-700">
                                                    <i x-bind:class="showPassword ? 'fa-solid fa-eye ' : 'fa-solid fa-eye-slash'"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <form @submit.prevent="submitPasswordForm" x-ref="submitPasswordForm">
                                            <div x-show="newPassword">
                                                <input type="hidden" name="user_id" x-model="user_id">
                                                <div class="flex flex-col justify-between gap-2">
                                                    <label for="newPasswords" class="font-semibold">New Password</label>
                                                    <input type="text" name="newPasswords" id="newPasswords" placeholder="Enter new password"
                                                        class="pl-2 p-2 border-2 shadow-inputShadow outline-none" x-model="newPasswords" @keyup="validateForm()"  x-ref="newPassword">
                                                </div>
                                                <div class="mt-2 mb-2 text-red-600 text-md">
                                                    <template x-for="error in newPasswordErrMsg" :key="error">
                                                        <p x-text="error"></p>
                                                    </template>
                                                </div>
                                                <div class="flex flex-col justify-between gap-2 mt-2 mb-3">
                                                    <label for="confirmPasswords" class="font-semibold">Confirm Password</label>
                                                    <input type="text" name="confirmPasswords" id="confirmPasswords" placeholder="Confirm password"
                                                        class="pl-2 p-2 border-2 shadow-inputShadow outline-none" x-model="confirmPasswords" @keyup="validateForm()" >
                                                </div>
                                                <div class="mt-2 mb-2 text-red-600 text-md">
                                                    <template x-for="error in confirmPasswordErrMsg" :key="error">
                                                        <p x-text="error"></p>
                                                    </template>
                                                </div>
                                                <div class="flex flex-row gap-2">
                                                    <button type="submit" class="bg-spryBlue w-36 text-white py-2">Save Password</button>
                                                    <button type="button" class="bg-gray-400 w-36 text-white py-2" x-on:click="newPassword = false;clearFormInput(); clearFormErrMsg(); focusNewPassword();">Cancel</button>
                                                </div>
                                            </div>
                                            <div x-show="!newPassword">
                                                <button type="button" class="bg-spryBlue w-36 text-white py-2"
                                                    x-on:click="newPassword = true; $nextTick(() => setTimeout(() => $refs.newPassword.focus(), 50));">
                                                    Change Password
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Centered Alert Box with Check Icon for Success -->
    <div x-show="alertMessage"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-75"
        class="fixed inset-0 flex items-center justify-center z-50">

        <div class="flex flex-col items-center gap-3 p-10 w-96 h-56 rounded-xl shadow-lg bg-white text-green-600"
            :class="alertType === 'success' ? 'text-green-600' : 'text-red-600'">

            <!-- Font Awesome Check Icon and Success Text -->
            <template x-if="alertType === 'success'">
                <div class="flex flex-col items-center gap-2 ">
                    <i class="fa-regular fa-circle-check text-6xl animate-bounce"></i>
                    <span class="font-bold text-4xl">Success</span>
                </div>
            </template>

            <!-- Alert Message -->
            <span class="text-lg font-semibold" x-text="alertMessage"></span>
        </div>
    </div>


    <!-- Blocker for modalShow -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-10"
        x-show="modalAdd || modalEdit || alertMessage">
    </div>
    <script>
        // Auto logout after 5 minutes of inactivity
        const autoLogoutTime = 5 * 60 * 1000; // 5 minutes = 300,000 ms
        let inactivityTimer;

        function resetTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                // Redirect to logout script
                window.location.href = 'databases/log-out.php'; // Adjust if your logout file is in a different path
            }, autoLogoutTime);
        }

        // Reset timer on these user interactions
        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetTimer);
        });

        resetTimer(); // Start the inactivity timer
    </script>

</body>

</html>