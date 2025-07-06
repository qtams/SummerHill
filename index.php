<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "styles/styles.php"; ?>
    <title>Login</title>
    <style>
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    .animate-float-slow {
        animation: float 12s ease-in-out infinite;
    }

    .animate-float-medium {
        animation: float 8s ease-in-out infinite;
    }

    .animate-float-fast {
        animation: float 5s ease-in-out infinite;
    }

    /* Custom delays for variation */
    .delay-500 { animation-delay: 0.5s; }
    .delay-700 { animation-delay: 0.7s; }
    .delay-1000 { animation-delay: 1s; }
    .delay-1200 { animation-delay: 1.2s; }
    .delay-1500 { animation-delay: 1.5s; }
    .delay-1800 { animation-delay: 1.8s; }
    .delay-2000 { animation-delay: 2s; }
    .delay-2500 { animation-delay: 2.5s; }
    .delay-3000 { animation-delay: 3s; }
</style>


</head>

<body x-data="{
    username: '',
    password: '',
    loading: false,
    welcomeMessage: '',
    errorMessage: '',
    showLogin: false,
    
    
    async login() {
    this.errorMessage = '';
    
    let formData = new FormData();
    formData.append('username', this.username);
    formData.append('password', this.password);
    
    try {
        let response = await fetch('databases/log-in.php', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        });
        
        let result = await response.json();
        console.log('Login Response:', result);
        
        if (result.status === 'success') {
            this.loading = true;
            
            setTimeout(() => {
                this.loading = false;
                this.showLogin = false;
                
                if (result.role === 'Admin') {
                    this.welcomeMessage = 'Welcome Admin!';
                } else {
                    this.welcomeMessage = `Welcome ${result.firstname}, ${result.lastname}!`;
                }
            }, 2000);
            
            setTimeout(() => {
                const welcomeEl = document.getElementById('welcomeMessage');
                welcomeEl.classList.add('opacity-0');
                
                setTimeout(() => {
                    console.log('Redirecting to dashboard...');
                    window.location.href = 'dashboard.php';
                }, 500);
            }, 3000);
            
        } else {
            this.errorMessage = result.message;
            setTimeout(() => this.errorMessage = '', 3000);
        }
    } catch (error) {
        this.errorMessage = 'An error occurred. Please try again.';
        setTimeout(() => this.errorMessage = '', 3000);
    }
}

}">

    <?php include "databases/connection.php"; ?>
    <!-- Enhanced Floating Tech Elements (Background) -->
    <div class="absolute inset-0 overflow-hidden opacity-20 -z-10">
        <!-- Orange Tones -->
        <div class="absolute top-10 left-10 w-16 h-16 bg-spryOrange rounded-full blur-md animate-float-slow"></div>
        <div class="absolute top-1/3 left-1/4 w-10 h-10 bg-orange-300 rounded-full blur-sm animate-float-medium"></div>
        <div class="absolute bottom-1/4 right-1/2 w-24 h-24 bg-orange-400 rounded-full blur-lg animate-float-fast"></div>
        <div class="absolute bottom-10 left-[5%] w-14 h-14 bg-orange-200 rounded-full blur-md animate-float-slow delay-2000"></div>

        <!-- Blue Tones -->
        <div class="absolute top-1/2 right-[5%] w-12 h-12 bg-spryBlue rounded-full blur-sm animate-float-medium delay-500"></div>
        <div class="absolute top-[15%] right-[30%] w-20 h-20 bg-blue-300 rounded-full blur-md animate-float-slow delay-1000"></div>
        <div class="absolute bottom-16 right-1/3 w-12 h-12 bg-blue-400 rounded-full blur-sm animate-float-fast delay-3000"></div>
        <div class="absolute bottom-[35%] left-[10%] w-10 h-10 bg-blue-200 rounded-full blur-lg animate-float-medium"></div>

        <!-- Purple / Indigo -->
        <div class="absolute top-[20%] left-[15%] w-16 h-16 bg-purple-300 rounded-full blur-md animate-float-medium"></div>
        <div class="absolute bottom-[10%] right-[10%] w-18 h-18 bg-indigo-400 rounded-full blur-sm animate-float-fast"></div>

        <!-- White Neutrals for contrast -->
        <div class="absolute top-2/3 left-1/3 w-8 h-8 bg-white rounded-full blur-sm opacity-40 animate-float-slow delay-1500"></div>
        <div class="absolute bottom-1/2 right-1/4 w-6 h-6 bg-white rounded-full blur-sm opacity-30 animate-float-fast delay-2500"></div>

        <!-- Extra Pops -->
        <div class="absolute top-[5%] left-[50%] w-20 h-20 bg-orange-300 rounded-full blur-lg animate-float-medium delay-1000"></div>
        <div class="absolute top-[75%] right-[15%] w-14 h-14 bg-blue-500 rounded-full blur-sm animate-float-slow delay-2000"></div>
        <div class="absolute bottom-[30%] left-[70%] w-18 h-18 bg-indigo-300 rounded-full blur-md animate-float-fast"></div>
        <div class="absolute top-[40%] left-[5%] w-16 h-16 bg-blue-200 rounded-full blur-sm animate-float-medium delay-700"></div>
        <div class="absolute bottom-[15%] right-[40%] w-20 h-20 bg-spryOrange rounded-full blur-lg animate-float-slow delay-1200"></div>
        <div class="absolute bottom-[5%] left-[40%] w-10 h-10 bg-purple-200 rounded-full blur-sm animate-float-fast delay-1800"></div>
    </div>



    <!-- Login Container -->
    <div id="loginContainer" x-show="showLogin" x-cloak
        x-init="setTimeout(() => showLogin = true, 2000)"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-white md:w-3/4 lg:w-[60%] md:h-2/3 shadow-loginShadow"
        x-transition:enter="transition ease-out duration-1000 transform"
        x-transition:enter-start="opacity-0 scale-50"
        x-transition:enter-end="opacity-100 scale-100">

        <div class="flex h-full flex-col md:flex-row">
            <!-- Logo Section -->
            <div class="w-full py-16 flex flex-col bg-yellow items-center justify-center md:rounded-tr-2xl md:rounded-br-2xl relative shadow-tableShadow">
                <!-- Logo Image with Animation -->
                <img src="images/CCSS.png" alt="Sprytech Logo" class="w-52 mb-4 md:mb-0"
                    x-data="{ show: false }"
                    x-cloak
                    x-init="setTimeout(() => show = true, 2500)"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-1000 transform"
                    x-transition:enter-start="opacity-0 scale-50"
                    x-transition:enter-end="opacity-100 scale-100">

                <!-- Text with Animation -->
                <div class="text-center mt-4"
                    x-cloak
                    x-data="{ showText: false }"
                    x-init="setTimeout(() => showText = true, 3000)"
                    x-show="showText"
                    x-transition:enter="transition ease-out duration-1000 transform"
                    x-transition:enter-start="opacity-0 scale-50"
                    x-transition:enter-end="opacity-100 scale-100">
                    <div class = "flex flex-col text-white gap-3">
                        <span class="text-4xl font-bold tracking-widest">CCSS</span>
                        <span class="text-4xl font-bold tracking-wider">LABORATORY</span>
                    </div>
                    
                </div>
            </div>

            <!-- Login Form -->
            <div class="w-full mt-5 md:mt-0 flex items-center justify-center px-4 sm:px-6 md:px-12">
                <div class="w-full">
                    <h1 class="font-bold text-2xl mb-4 text-modifGreen">SIGN IN</h1>
                    <form @submit.prevent="login">
                        <div class="flex flex-col gap-2 mb-4">
                            <label for="username" class="font-semibold block text-modifGreen">Username</label>
                            <div class="relative flex items-center">
                                <input type="text" id="username" class="w-full pl-2 p-2 border-2 shadow-inputShadow outline-none mt-2" placeholder="Enter username" x-model="username" required>
                                <i class="absolute top-6 right-4 fa-solid fa-user"></i>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="password" class="font-semibold block text-modifGreen">Password</label>
                            <div class="relative flex items-center" x-data="{ show: false }">
                                <input type="password" x-bind:type="show ? 'text' : 'password'" class="w-full pl-2 p-2 border-2 shadow-inputShadow outline-none mt-2" placeholder="Enter Password" x-model="password" required>
                                <i class="absolute top-6 right-4 fa-solid" x-on:click="show = !show" x-bind:class="show ? 'fa-eye' : 'fa-eye-slash'"></i>
                            </div>
                        </div>
                       

                        <button class="w-full p-2 bg-ccssGreen text-white font-semibold mt-5">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Alert -->
    <div x-show="errorMessage" x-cloak
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150 transform"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-75"
        class="fixed top-5 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded shadow-lg bg-red-500 text-white">
        <span x-text="errorMessage"></span>
    </div>

    <!-- Loading Animation -->
    <div x-show="loading" x-cloak
        class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-80 z-50"
        x-transition:enter="transition-opacity ease-out duration-500"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-500"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="flex flex-col items-center">
            <div class="relative">
                <div class="w-16 h-16 rounded-full border-4 border-orange-500 border-t-transparent animate-spin"></div>
                <div class="absolute inset-0 w-16 h-16 rounded-full border-4 border-orange-500 opacity-50 animate-ping"></div>
            </div>
            <p class="mt-4 text-lg font-semibold text-gray-700">Logging in...</p>
        </div>
    </div>

    <!-- Welcome Message -->
    <div id="welcomeMessage" x-show="welcomeMessage" x-text="welcomeMessage"
        class="fixed inset-0 flex items-center justify-center bg-white text-5xl font-bold text-orange-500 transition-opacity duration-500"
        x-transition:enter="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="opacity-100"
        x-transition:leave-end="opacity-0">
    </div>
</body>

</html>