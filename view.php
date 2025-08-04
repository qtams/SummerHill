<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Information</title>
    <?php include "styles/styles.php"; ?>
</head>

<body x-data="{
    card_id: '',
    student_id: '',
    showError: false,
    errorMessage: '',
    cooldownMap: {},
    cooldownInterval: null,
    showCooldownMessage: false,
    cooldownSecondsLeft: 0,
    cooldownStudentId: '',
    showStudentIdModal: false, // Add modal state

    student: {
        student_id: '',
        lastname: '',
        year_level: '',
        firstname: '',
        profile: '',
        email: '',
    },

    get fullName() {
        return [this.student.firstname, this.student.lastname]
            .filter(Boolean)
            .join(' ')
            .toUpperCase() || 'N/A';
    },

    getTraineeDetails() {
            if (this.card_id.length !== 10) return;

            const scanned_id = this.card_id;
            this.card_id = '';
            this.$nextTick(() => document.getElementById('card_id').focus());

            const now = Date.now();
            const cooldown = this.cooldownMap[scanned_id];

            if (cooldown && now < cooldown) {
                this.cooldownSecondsLeft = Math.ceil((cooldown - now) / 1000);
                this.cooldownStudentId = scanned_id;
                this.showCooldownMessage = true;
                setTimeout(() => this.showCooldownMessage = false, 3000);
                return;
            }

            this.cooldownMap[scanned_id] = now + 30000;
            this.startCooldownTimer();

            fetch(`get-table-data.php?get_data=trainee_details&card_id=${scanned_id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        this.resetStudentData();
                        this.errorMessage = data.error;
                        this.showError = true;
                        setTimeout(() => {
                            this.showError = false;
                            this.errorMessage = '';
                        }, 3000);
                    } else {
                        this.student = data;

                        // ✅ Only send if valid email exists
                        if (data.email) {
                            this.sendEmail(scanned_id, data.email);
                        } else {
                            console.log('No email found for this trainee.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.resetStudentData();
                    this.errorMessage = 'Network error occurred';
                    this.showError = true;
                    setTimeout(() => {
                        this.showError = false;
                        this.errorMessage = '';
                    }, 3000);
                });
        },

        sendEmail(card_id, email) {
            fetch('send-email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ card_id: card_id, email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Email sent successfully.');
                } else {
                    console.error('Email send failed:', data.message);
                }
            })
            .catch(error => {
                console.error('Email send error:', error);
            });
    },

    getStudentDetails() {
        if (this.student_id.length !== 11) return;

        const input_id = this.student_id;
        this.student_id = '';
        this.showStudentIdModal = false; // Close modal after input
        this.$nextTick(() => document.getElementById('card_id').focus());

        const now = Date.now();
        const cooldown = this.cooldownMap[input_id];

        if (cooldown && now < cooldown) {
            this.cooldownSecondsLeft = Math.ceil((cooldown - now) / 1000);
            this.cooldownStudentId = input_id;
            this.showCooldownMessage = true;
            setTimeout(() => this.showCooldownMessage = false, 3000);
            return;
        }

        this.cooldownMap[input_id] = now + 30000;
        this.startCooldownTimer();

        fetch(`get-table-data.php?get_data=student_id&student_id=${input_id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    this.resetStudentData();
                    this.errorMessage = data.error;
                    this.showError = true;
                    setTimeout(() => {
                        this.showError = false;
                        this.errorMessage = '';
                    }, 3000);
                } else {
                    this.student = data;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.resetStudentData();
                this.errorMessage = 'Network error occurred';
                this.showError = true;
                setTimeout(() => {
                    this.showError = false;
                    this.errorMessage = '';
                }, 3000);
            });
    },

    resetStudentData() {
        this.student = {
            student_id: '',
            lastname: '',
            year_level: '',
            firstname: '',
            profile: ''
        };
    },

    startCooldownTimer() {
        if (this.cooldownInterval) return;

        this.cooldownInterval = setInterval(() => {
            const now = Date.now();
            if (this.cooldownStudentId && this.cooldownMap[this.cooldownStudentId]) {
                const remaining = this.cooldownMap[this.cooldownStudentId] - now;
                this.cooldownSecondsLeft = Math.max(Math.ceil(remaining / 1000), 0);
                if (remaining <= 0) {
                    clearInterval(this.cooldownInterval);
                    this.cooldownInterval = null;
                    this.cooldownStudentId = '';
                    this.cooldownSecondsLeft = 0;
                }
            }
        }, 1000);
    },

    init() {
        this.$nextTick(() => document.getElementById('card_id').focus());
        setInterval(() => this.resetStudentData(), 2000);
        document.addEventListener('click', () => {
            this.$nextTick(() => document.getElementById('card_id').focus());
        });
        // Listen for Tab key to open manual input modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab' && !this.showStudentIdModal) {
                e.preventDefault();
                this.showStudentIdModal = true;
                this.$nextTick(() => {
                    const modalInput = document.getElementById('student_id_modal');
                    if (modalInput) modalInput.focus();
                });
            }
        });
    }
}" x-init="init()" x-cloak>

    <div class="w-full h-screen bg-Sblue px-10 flex flex-col">
        <div>
            <input type="text"
                class="pl-2 border-2 shadow-inputShadow outline-none ml-10 opacity-0"
                name="card_id"
                id="card_id"
                placeholder="Student ID"
                x-model="card_id"
                maxlength="10"
                x-on:input.debounce.500ms="if (card_id.length === 10) getTraineeDetails()">
        </div>

        <!-- Student ID Modal -->
        <div x-show="showStudentIdModal" class="fixed inset-0 bg-gray-800 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg shadow-xl w-96 flex flex-col items-center">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Please input Student ID</h2>
                <input type="text"
                    class="pl-2 border-2 shadow-inputShadow outline-none w-full mb-4"
                    name="student_id"
                    id="student_id_modal"
                    placeholder="Student ID"
                    x-model="student_id"
                    maxlength="11"
                    @keydown.stop
                    @click.stop>
                <div class="flex gap-4">
                    <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded" @click="getStudentDetails()" :disabled="student_id.length !== 11">
                        Submit
                    </button>
                    <button type="button" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded" @click="showStudentIdModal = false">
                        Cancel
                    </button>
                </div>
            </div>
        </div>


        <div class="items-center justify-center flex flex-col ">
            <span class=" font-bold text-2xl text-Syellow">SUMMERHILL SCHOOL FOUNDATION, INC.</span>
            <span class=" font-bold text-4xl text-Syellow">COMPUTERIZED TIME IN TIME OUT SYSTEM</span>
        </div>

        <div class="flex-1 flex items-center justify-center">
            <div class="flex items-center gap-20">
                <div class="flex flex-col items-center justify-center">
                    <img src="images/SummerhillLogo.jpg" alt="Summerhill Logo" class="w-96 object-cover rounded-full">

                    <div class="font-bold text-md text-dashColor text-center" x-data="{ date: '', time: '' }" x-init="
                            const optionsDate = { timeZone: 'Asia/Manila', year: 'numeric', month: 'long', day: 'numeric' };
                            const optionsTime = { timeZone: 'Asia/Manila', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true };
                            const updateDateTime = () => {
                                const now = new Date();
                                date = now.toLocaleString('en-US', optionsDate).toUpperCase();
                                time = now.toLocaleString('en-US', optionsTime).toUpperCase();
                            };
                            updateDateTime();
                            setInterval(updateDateTime, 1000);
                        ">
                        <div><span class="text-white text-3xl" x-text="date"></span></div>
                        <div><span class="text-white text-3xl" x-text="time"></span></div>
                    </div>
                </div>

                <div class="flex flex-col items-center">
                    <div class="flex flex-col items-center justify-center shadow-loginShadow w-96 h-96 bg-gray-100 overflow-hidden rounded-xl" x-cloak>
                        <img :src="student.profile ? student.profile : 'images/images.png'"
                            alt="Profile Picture"
                            class="w-full h-full object-cover">
                    </div>
                    <div class="flex flex-col mt-3 text-white items-center font-bold text-lg">
                        <span x-text="student.student_id || 'N/A'"></span>
                        <span x-text="student.year_level || 'N/A'"></span>
                        <span x-text="student.email || 'N/A'"></span>
                        <span x-text="fullName"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showError" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-md" x-cloak>
        <div class="bg-white p-5 rounded-lg shadow-xl text-center">
            <p class="text-xl font-semibold text-red-600" x-text="errorMessage"></p>
        </div>
    </div>

    <div x-show="showCooldownMessage" x-transition.duration.300ms
        class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-5 py-3 rounded-lg shadow-lg z-50 text-lg font-semibold"
        x-cloak>
        You already tapped! Try again in <span x-text="cooldownSecondsLeft"></span>s.
    </div>

   <div
  x-data="{
    showSaver: false,
    timeout: null,
    resetTimer() {
      this.showSaver = false;
      clearTimeout(this.timeout);
      this.timeout = setTimeout(() => this.showSaver = true, 10000);
    }
  }"
  x-init="
    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(event => {
      window.addEventListener(event, () => resetTimer());
    });
    resetTimer();
  "
  x-effect="if (showStudentIdModal) showSaver = false"
>
  <div
    x-show="showSaver && !showStudentIdModal"
    x-transition:enter="transition-opacity duration-700"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-700"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gradient-to-r from-yellow-400 via-yellow-300 to-blue-500">
    <img src="Video/3D.gif" alt="3D Logo" class="w-[800px] object-contain">
  </div>
</div>




</body>

</html>