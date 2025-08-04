<!DOCTYPE html>
<html lang="en"
    x-data="{
    student: {
        student_id: '',
        card_id: '',
        lastname: '',
        firstname: '',
        section: '',
        year_level: ''
    },
    email: '',
    messenger_link: '',
    errorMessage: '',
    emailErrMsg: [],
    hasError: false,

    alertMessage: '',
    alertType: '',
    showContact: false,

    fetchStudentDetails(student_id) {
        if (student_id.trim() === '') {
            this.clearStudent();
            this.showContact = false;
            return;
        }
        if (student_id.length < 4) {
            this.clearStudent();
            this.showContact = false;
            this.errorMessage = 'Student ID must be at least 4 characters';
            return;
        }

        fetch(`get-table-data.php?get_data=students&student_id=${student_id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    this.clearStudent();
                    this.showContact = false;
                    this.errorMessage = data.error;
                } else {
                    this.student = {
                        student_id: data.student_id,
                        card_id: data.card_id,
                        lastname: data.lastname,
                        firstname: data.firstname,
                        section: data.section,
                        year_level: data.year_level
                    };
                    this.errorMessage = '';
                    this.showContact = true; // show email/social inputs
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                this.errorMessage = 'Error fetching student data';
                this.showContact = false;
            });
    },

    clearStudent() {
        this.student = { student_id: '', card_id: '', lastname: '', firstname: '', section: '', year_level: '' };
    },

    validateForm() {
  this.emailErrMsg = [];
  this.hasError = false;

  if (this.email.trim() === '') {
    this.hasError = true;
    this.emailErrMsg.push('*Email is required.');
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) {
    this.hasError = true;
    this.emailErrMsg.push('*Please enter a valid email address.');
  }

  if (this.messenger_link.trim() === '') {
    this.hasError = true;
    alert('*Messenger Link / PSID is required.');
  }
},



    submitStudentForm(event) {
        event.preventDefault();
        this.validateForm();

        if (this.hasError) {
            console.log('Form has errors, submission stopped!');
            return;
        }

        const formData = new FormData();
        formData.append('student_id', this.student.student_id);
        formData.append('email', this.email);
        formData.append('social', this.messenger_link);

        fetch('databases/add-information.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                this.alertMessage = 'Parent form saved successfully!';
                this.alertType = 'success';

                // Clear all inputs
                this.clearStudent();
                this.email = '';
                this.messenger_link = '';
                this.showContact = false;

                // Also clear Student ID input box manually
                const studentInput = document.querySelector('input[name=\'student_id\']');
                if (studentInput) studentInput.value = '';

                setTimeout(() => {
                    this.alertMessage = '';
                }, 2000);
            } else {
                this.alertMessage = data.message || 'Something went wrong.';
                this.alertType = 'error';
                setTimeout(() => {
                    this.alertMessage = '';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.alertMessage = 'Submission failed.';
            this.alertType = 'error';
            setTimeout(() => {
                this.alertMessage = '';
            }, 2000);
        });
    }
}"
    x-cloak>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Parent Student Form</title>
    <?php include "styles/styles.php"; ?>
</head>

<body>
    <div class="w-full md:w-[70%] lg:w-[50%] shadow-tableShadow mx-auto mt-24 pb-5">
        <div class="flex items-center justify-center text-white bg-orange-500 p-5 rounded-t-1xl">
            <h1 class="font-bold text-2xl mb-2">Parent Form - Student Details</h1>
        </div>

        <div class="px-4 md:px-20">
            <form @submit="submitStudentForm">

                <!-- Student ID -->
                <div class="flex flex-col gap-2 mt-5">
                    <label class="font-semibold">Student ID</label>
                    <input type="text"
                        name="student_id"
                        class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                        placeholder="Enter Student ID"
                        x-on:input="fetchStudentDetails($event.target.value)"
                        required>
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage" class="text-red-500 text-sm mt-2">
                    <span x-text="errorMessage"></span>
                </div>

                <!-- Card ID -->
                <div class="flex flex-col gap-2 mt-3">
                    <label class="font-semibold">Card ID</label>
                    <input type="text"
                        name="card_id"
                        class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                        x-model="student.card_id"
                        disabled>
                </div>

                <!-- Last Name -->
                <div class="flex flex-col gap-2 mt-3">
                    <label class="font-semibold">Last Name</label>
                    <input type="text"
                        name="lastname"
                        class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                        x-model="student.lastname"
                        disabled>
                </div>

                <!-- First Name -->
                <div class="flex flex-col gap-2 mt-3">
                    <label class="font-semibold">First Name</label>
                    <input type="text"
                        name="firstname"
                        class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                        x-model="student.firstname"
                        disabled>
                </div>

                <!-- Section -->
                <div class="flex flex-col gap-2 mt-3">
                    <label class="font-semibold">Section</label>
                    <input type="text"
                        name="section"
                        class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                        x-model="student.section"
                        disabled>
                </div>

                <!-- Grade Level -->
                <div class="flex flex-col gap-2 mt-3">
                    <label class="font-semibold">Grade Level</label>
                    <input type="text"
                        name="year_level"
                        class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                        x-model="student.year_level"
                        disabled>
                </div>

                <!-- Contact Info: only if student found -->
                <div x-show="showContact" x-transition>
                    <!-- Email -->
                    <div class="flex flex-col gap-2 mt-3">
                        <label class="font-semibold">Email</label>
                        <input type="text"
                            name="email"
                            x-model="email"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            placeholder="Enter Email">
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in emailErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- Messenger PSID -->
                    <div class="flex flex-col gap-2 mt-3">
                        <label class="font-semibold">Messenger Link or PSID</label>
                        <input type="text"
                            name="messenger_link"
                            x-model="messenger_link"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            placeholder="Paste Messenger Link or PSID here"
                            required>
                    </div>
                </div>

                <!-- Submit -->
                <div class="mt-6">
                    <button type="submit"
                        class="w-full p-4 text-white font-semibold mb-3 bg-orange-500 hover:bg-orange-600"
                        :disabled="errorMessage !== ''">
                        Save Parent Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Box -->
    <div x-show="alertMessage"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-75"
        class="fixed inset-0 flex items-center justify-center z-50">

        <div class="flex flex-col items-center gap-3 p-10 w-96 h-64 rounded-xl shadow-lg bg-white"
            :class="alertType === 'success' ? 'text-green-600' : 'text-red-600'">

            <template x-if="alertType === 'success'">
                <div class="flex flex-col items-center gap-2">
                    <i class="fa-regular fa-circle-check text-6xl animate-bounce"></i>
                    <span class="font-bold text-4xl">Success</span>
                </div>
            </template>

            <template x-if="alertType === 'error'">
                <div class="flex flex-col items-center gap-2">
                    <i class="fa-solid fa-circle-xmark text-6xl animate-bounce"></i>
                    <span class="font-bold text-4xl">Error</span>
                </div>
            </template>

            <span class="text-lg text-center font-semibold" x-text="alertMessage"></span>
        </div>
    </div>

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-10"
        x-show="alertMessage"></div>

</body>

</html>