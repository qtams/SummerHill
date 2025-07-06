<!-- Alpine.js Initialization -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>


<!-- sidebar -->
<div
    class="fixed top-0 left-0 h-screen shadow-tableShadow text-white bg-Sblue duration-500 z-10"
    x-bind:class="open ? 'w-64' : ('md:w-20','lg:w-24')">

    <!-- nav header -->
    <div class="relative h-32 mt-6">
        <!-- Logo (centered absolutely) -->
        <div class="absolute inset-0 flex justify-center items-center" x-show="open">
            <div class="shadow-pressDownDeep w-32 h-32 rounded-full flex justify-center items-center">
                <img src="images/SummerhillLogo.jpg" alt="Sprytech Logo"
                    class="w-28 h-28 rounded-full object-cover transition-transform duration-300 hover:scale-105" />
            </div>
        </div>

        <!-- Toggle Button -->
        <i class="fa-solid fa-bars absolute -right-6 top-8 bg-Sblue p-3 rounded-full cursor-pointer 
        hover:scale-110 transition-all duration-300 text-xl"
            x-on:click="open = !open"
            :class="open ? 'rotate-90' : ''"
            style="min-width: 2.5rem; min-height: 2.5rem; display: flex; justify-content: center; align-items: center;">
        </i>
    </div>


    <!-- nav items -->
    <ul class="mt-12 px-5 flex flex-col gap-2 " x-bind:class="open ? 'items-center'">
        <!-- Common Links for All Roles -->
        <li class="nav-list relative group  shadow-pressDownDeep rounded-lg">
            <a href="dashboard.php" class="flex h-12 items-center hover:bg-Syellow hover:text-Sdarkblue rounded-lg p-3 transition-transform duration-300 hover:scale-[1.02]"
                :class="open ? 'gap-5 px-4' : 'justify-center'">
                <i class="fa-solid fa-house w-5 text-center"></i>
                <span x-show="open" class="flex-1 font-semibold">Dashboard</span>
                <span x-show="!open" class="absolute left-full ml-4 px-2 py-4 w-28 text-center bg-Sblue text-white text-sm  opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity duration-300">
                    Dashboard
                </span>
            </a>
        </li>

        <li class="nav-list relative group  shadow-pressDownDeep rounded-lg">
            <a href="profile.php" class="flex h-12 items-center hover:bg-Syellow hover:text-Sdarkblue rounded-lg p-3 transition-transform duration-300 hover:scale-[1.02]"
                :class="open ? 'gap-5 px-5' : 'justify-center'">
                <i class="fa-solid fa-circle-user w-5 text-center"></i>
                <span x-show="open" class="flex-1 font-semibold">Profile</span>
                <span x-show="!open" class="absolute left-full ml-4 px-2 py-4 bg-Sblue w-28 text-center text-white text-sm  opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity duration-300">
                    Profile
                </span>
            </a>
        </li>

        <li class="nav-list relative group shadow-pressDownDeep rounded-lg">
            <a href="students.php" class="flex h-12 items-center hover:bg-Syellow hover:text-Sdarkblue rounded-lg p-3 transition-transform duration-300 hover:scale-[1.02]"
                :class="open ? 'gap-5 px-5' : 'justify-center'">
                <i class="fa-solid fa-person-circle-question w-5 text-center"></i>
                <span x-show="open" class="flex-1 font-semibold">Students</span>
                <span x-show="!open" class="absolute left-full ml-4 px-2 py-4 bg-Sblue w-28 text-center text-white text-sm  opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity duration-300">
                    Students
                </span>
            </a>
        </li>

        <li class="nav-list relative group shadow-pressDownDeep rounded-lg">
            <a href="time_monitoring.php" class="flex h-12 items-center hover:bg-Syellow hover:text-Sdarkblue rounded-lg p-3 transition-transform duration-300 hover:scale-[1.02]"
                :class="open ? 'gap-5 px-5' : 'justify-center'">
                <i class="fa-solid fa-person-circle-question w-5 text-center"></i>
                <span x-show="open" class="flex-1 font-semibold">In / Out View</span>
                <span x-show="!open" class="absolute left-full ml-4 px-2 py-4 bg-Sblue w-28 text-center text-white text-sm  opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity duration-300">
                    In / Out View
                </span>
            </a>
        </li>

        <!-- Logout (Visible to All) -->
        <li class="nav-list relative group shadow-pressDownDeep rounded-lg">
            <a href="databases/log-out.php" class="flex h-12 items-center hover:bg-Syellow hover:text-Sdarkblue rounded-lg p-3 transition-transform duration-300 hover:scale-[1.02]"
                :class="open ? 'gap-5 px-5' : 'justify-center'">
                <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                <span x-show="open" class="flex-1 font-semibold">Log Out</span>
                <span x-show="!open" class="absolute left-full ml-4 px-2 py-4 bg-Sblue w-28 text-center text-white text-sm  opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity duration-300">
                    Log Out
                </span>
            </a>
        </li>
    </ul>
</div>