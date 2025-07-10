<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers</title>
    <?php include "styles/styles.php" ?>

</head>

<body x-data="{
        modalAdd: false,
        modalEdit: false,
        showAlert: false,
        alertMessage: '',
        teacherList: [],
        paginatedList: [],
        perPage: 5,
        currentPage: 1,
        totalPages: 1,
        card_id: '',
        teacher_id: '',
        lastname: '',
        firstname: '',
        year_level: '',
        social: '',
        email: '',
        mobile: '',
        profile: '',
        status: '',
        statusFilter: 'all',
        searchTerm: '',
        sortBy: '',
        sortAsc: true,
        selectedTeachers: [],
        selectAll: false,
        teacherErrMsg:[],
        lastnameErrMsg:[],
        firstnameErrMsg:[],
        teacherErrMsg:[],
        cardErrMsg:[],
        emailErrMsg: [],
        mobileErrMsg: [],
        socialErrMsg: [],
        alerType: '',
        errMsg: '',
        hasError: false,


        showAlert(message, type = 'success') {
            this.alertMessage = message;
            this.alertType = type;
            setTimeout(() => this.alertMessage = '', 3000); // Hide alert after 3 seconds
        },


        getTeacher() {
            fetch('get-table-data.php?get_data=teacher')
                .then(response => response.json())
                .then(data => {
                    this.teacherList = data;
                    window.teacherList = data; // ✅ This line is required for export to work
                    console.log(data); // Do what you want with teacher data here
                    // Apply filters without resetting the page
                    this.applyFilters(false); // Pass false to indicate we don't want to reset the page
                    
                    // Restore the current page if it's still valid
                    if (currentPageBeforeRefresh <= this.totalPages) {
                        this.currentPage = currentPageBeforeRefresh;
                    } else if (this.totalPages > 0) {
                        // If the old page is no longer valid, go to the last page
                        this.currentPage = this.totalPages;
                    }

                    this.paginate();
                    window.teacherList = this.teacherList;


                    // Example: display teacher names in console
                    data.forEach(teacher => {
                        console.log(teacher.name); // adjust property based on your DB columns
                    });
                })
                .catch(error => {
                    console.error('Error fetching teacher data:', error);
                });
        },

        updateStatus(teacher_id, status) {
            fetch('databases/update-teacher-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    teacher_id: teacher_id,
                    status: status 
                }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    this.showAlert('Status updated successfully.', 'success');
                    this.getTeacher(); // Refresh the list
                } else {
                    this.showAlert(data.message || 'Failed to update status.', 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                this.showAlert('An error occurred.', 'error');
            });
        },

        applyFilters(resetPage = true) {
            let filteredList = this.teacherList;

            // Filter by search term across all specified fields
            if (this.searchTerm) {
                const term = this.searchTerm.toLowerCase();
                filteredList = filteredList.filter(teacher =>
                    (teacher.teacher_id && teacher.teacher_id.toString().toLowerCase().includes(term)) ||
                    (teacher.firstname && teacher.firstname.toLowerCase().includes(term)) ||
                    (teacher.lastname && teacher.lastname.toLowerCase().includes(term)) ||
                    (teacher.year_level && teacher.year_level.toLowerCase().includes(term)) 
                    
                );
            }

            // Filter by status
            if (this.statusFilter && this.statusFilter !== 'all') {
                filteredList = filteredList.filter(teacher => 
                    teacher.status === this.statusFilter
                );
            }

            // Sort the list
            if (this.sortBy) {
                filteredList = filteredList.sort((a, b) => {
                    let aValue = a[this.sortBy];
                    let bValue = b[this.sortBy];

                    if (this.sortBy === 'grade') {
                        let indexA = gradeOrder.indexOf(aValue);
                        let indexB = gradeOrder.indexOf(bValue);

                        if (indexA === -1) indexA = gradeOrder.length; // Place unknown values last
                        if (indexB === -1) indexB = gradeOrder.length;

                        return this.sortAsc ? indexA - indexB : indexB - indexA;
                    }

                    // Default sorting for other columns
                    if (aValue < bValue) return this.sortAsc ? -1 : 1;
                    if (aValue > bValue) return this.sortAsc ? 1 : -1;
                    return 0;
                });
            }

            // Only reset page if explicitly requested (like when manually changing filters)
            if (resetPage) {
                this.currentPage = 1;
            }
            
            this.totalPages = Math.ceil(filteredList.length / this.perPage) || 1;
            this.filteredList = filteredList; // Store globally
            this.paginate();
        },

        setSort(column) {
            if (this.sortBy === column) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortBy = column;
                this.sortAsc = true;
            }
            this.applyFilters();
        },
        

        paginate() {
                    this.perPage = Number(this.perPage); // Ensure it's always a number

                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;

                    console.log('DEBUG - Current Page:', this.currentPage);
                    console.log('DEBUG - Per Page:', this.perPage, typeof this.perPage);
                    console.log('DEBUG - Start Index:', start, 'End Index:', end);

                    this.paginatedList = (this.filteredList || this.teacherList).slice(start, end);
                },

                changePage(page) {
                    if (page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                        console.log('Changing to Page:', page);
                        this.paginate();
                    }
                },

                getPaginationRange() {
                    let range = [];
                    let start = Math.max(1, this.currentPage - 2);
                    let end = Math.min(this.totalPages, this.currentPage + 2);

                    if (start > 1) range.push(1);
                    if (start > 2) range.push('...');

                    for (let i = start; i <= end; i++) {
                        range.push(i);
                    }

                    if (end < this.totalPages - 1) range.push('...');
                    if (end < this.totalPages) range.push(this.totalPages);

                    return range;
                },
                validateForm() {
            this.lastnameErrMsg = [];
            this.firstnameErrMsg = [];
            this.mobileErrMsg=[];
            this.emailErrMsg=[];
            this.teacherErrMsg = [];
            this.cardErrMsg = [];
            this.hasError = false;

            // Last name validation
            if (!/^[a-zA-Z ]*$/.test(this.lastname)) {
                this.hasError = true;
                this.lastnameErrMsg.push('*Last name can only contain letters.');
            }

            // First name validation
            if (!/^[a-zA-Z ]*$/.test(this.firstname)) {
                this.hasError = true;
                this.firstnameErrMsg.push('*First name can only contain letters.');
            }
            //  Mobile Validation
            if (this.mobile.trim() !== '' && !/^[0-9]+$/.test(this.mobile)) {
                this.hasError = true;
                this.mobileErrMsg.push('*Mobile Number can only contain numbers.');
            }
             // Email Validation
            if (this.email.trim() !== '' && !/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(this.email)) {
                this.hasError = true;
                this.emailErrMsg.push('*email contains @gmail.com at the end.');
            }

            // Teacher ID validation
            if (this.teacher_id.trim() !== '' && !/^[0-9]+$/.test(this.teacher_id)) {
                this.hasError = true;
                this.teacherErrMsg.push('*Teacher ID can only contain numbers.');
            }
            // Card ID validation
            if (this.card_id.trim() !== '' && !/^[0-9]+$/.test(this.card_id)) {
                this.hasError = true;
                this.cardErrMsg.push('*Card ID can only contain numbers.');
            }
            
            

        },
                submitTeacherForm(event) {
            event.preventDefault();
            this.validateForm();

            if (this.hasError) {
                console.log('Form has errors, submission stopped!');
                return;
            }
            console.log('card ID:', this.card_id);
            console.log('teacher ID:', this.teacher_id);
            console.log('Last Name:', this.lastname);
            console.log('First Name:', this.firstname);
            console.log('Email:', this.email);
            console.log('Social:', this.social);
            console.log('Mobile:', this.mobile);


            const fileInput = event.target.querySelector('input[name=\'profile\']');
            const selectedFile = fileInput?.files[0];

            if (selectedFile) {
                console.log('Profile:', selectedFile.name);
                console.log('File Type:', selectedFile.type);
                console.log('File Size:', selectedFile.size + ' bytes');
            } else {
                console.log('No profile picture selected.');
            }

            const formData = new FormData(event.target);

            fetch('databases/add-teachers.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.alertMessage = data.message;
                this.alertType = data.status;

                if (data.status === 'success') {
                    this.modalAdd = false;
                    this.getTeacher();
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

        toggleAll() {
            if (this.selectAll) {
                // Use the already filtered list from applyFilters()
                let filteredTeacherIds = this.filteredList.map(teacher => teacher.teacher_id);
                
                // Merge selected teacher, avoiding duplicates
                this.selectedTeachers = [...new Set([...this.selectedTeachers, ...filteredTeacherIds])];
            } else {
                // Deselect only filtered teacher, keep selections from other pages
                let filteredTeacherIds = this.filteredList.map(teacher => teacher.teacher_id);
                
                this.selectedTeachers = this.selectedTeachers.filter(id => !filteredTeacherIds.includes(id));
            }
        },

        toggleSelection(teacher_id) {
            if (this.selectedTeachers.includes(teacher_id)) {
                this.selectedTeachers = this.selectedTeachers.filter(id => id !== teacher_id);
            } else {
                this.selectedTeachers.push(teacher_id);
            }
        },

        deleteSelectedTeachers() {
            if (this.selectedTeachers.length === 0) {
            console.warn('No Teacher selected for deletion.');
            return;
            }

            console.log('Sending teacher_id:', JSON.stringify(this.selectedTeachers));

            if (!confirm(`Are you sure you want to delete ${this.selectedTeachers.length} teacher(s)?`)) return;

            fetch('databases/delete-selected-teacher.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `teacher_id=${encodeURIComponent(JSON.stringify(this.selectedTeachers))}`
            })
            .then(response => response.text())
            .then(text => {
                console.log('Raw Server Response:', text);
                let data;

                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    this.showAlert('Invalid JSON response from server.', 'error');
                    return;
                }

                console.log('Parsed Server Response:', data);

                if (data.status === 'success') {
                    this.teacherList = this.teacherList.filter(t => !this.selectedTeachers.includes(t.teacher_id));
                    this.selectedTeachers = [];
                    this.applyFilters();
                    this.selectAll = false;
                    this.showAlert('teacher(s) deleted successfully.', 'success', 'text-xs');
                } else {
                    this.showAlert(data.message || 'Failed to delete selected teacher(s).', 'error');
                }
            })
            .catch(error => {
                console.error('Bulk Delete Error:', error);
                this.showAlert('An error occurred while deleting.', 'error');
            });
        },

        submitEditForm(event) {
            event.preventDefault();
            this.validateForm();

            if (this.hasError) {
                console.log('Form has errors, submission stopped!');
                return;
            }

            const formData = new FormData(event.target);

            fetch('databases/edit-teacher.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server Response:', data);  // Log server response

                this.alertMessage = data.message;
                this.alertType = data.status;

                if (data.status === 'success') {
                    this.modalEdit = false;
                    this.clearFormInput();
                    this.clearFormErrMsg();
                    this.getTeacher();
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

        clearFormErrMsg() {
            this.hasError = false;
            this.lastnameErrMsg = [];
            this.firstnameErrMsg = [];
            this.teacherErrMsg = [];
            this.mobileErrMsg = [];
            this.emailErrMsg = [];
            this.cardErrMsg = [];
        },

        clearFormInput() {
            this.lastname = ''; 
            this.firstname = '';
            this.teacher_id = '';
            this.profile = '';
            this.year_level = '';
            this.email = '';
            this.mobile = '';
            this.card_id = '';
            const fileInput = this.$refs.teacherAddForm.querySelector('input[name=\'profile\']');
            if (fileInput) fileInput.value = '';
        },


        deleteTeacher(teacher) {
            if (!confirm(`Are you sure you want to teacher ${teacher.firstname} ${teacher.lastname}?\nAll related time monitoring records will also be deleted.`)) {
                return;
            }

            fetch('databases/delete-teacher.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ card_id: teacher.card_id }),
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.teacherList = this.teacherList.filter(t => t.card_id !== teacher.card_id);
                    this.applyFilters();
                    this.alertMessage = 'Teacher and related records deleted successfully!';
                    this.alertType = 'success';
                } else {
                    this.alertMessage = result.message || 'Failed to delete Teacher.';
                    this.alertType = 'error';
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

            confirmImport() {
            if (confirm('Are you sure you want to upload this file?')) {
                const formData = new FormData(document.getElementById('importForm'));
                fetch('databases/import-teachers.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text()) // Get raw text for debugging
                .then(text => {
                    let result;
                    try {
                        result = JSON.parse(text);
                    } catch (e) {
                        console.error('Raw server response (not valid JSON):', text); // <-- See PHP/HTML errors here
                        alert('Server did not return valid JSON. See console for details.');
                        document.getElementById('import_file').value = '';
                        return;
                    }
                    // Show debug info in the console
                    if (result.debug) {
                        console.log('PHP Debug:', result.debug);
                    }
                    if (result.success) {
                        alert(result.message); // Success message
                        if (result.errors && result.errors.length > 0) {
                            result.errors.forEach((error, index) => {
                                setTimeout(() => {
                                    alert(`Error ${index + 1} of ${result.errors.length}:\n${error}`);
                                }, index * 500);
                            });
                            setTimeout(() => {
                                window.location.reload(); // Reload after errors
                            }, result.errors.length * 500 + 500);
                        } else {
                            window.location.reload(); // No errors, reload immediately
                        }
                    } else {
                        alert(result.message); // General error message
                        if (result.errors && result.errors.length > 0) {
                            result.errors.forEach((error, index) => {
                                setTimeout(() => {
                                    alert(`Error ${index + 1} of ${result.errors.length}:\n${error}`);
                                }, index * 500);
                            });
                        }
                    }
                    document.getElementById('import_file').value = '';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during the file upload.');
                    document.getElementById('import_file').value = '';
                });
            } else {
                alert('File upload canceled.');
            }
        },



}" x-init="getTeacher()" x-cloak>

    <div>
        <div class="flex h-screen w-full" x-data="{ open: true }" x-bind:class="modalAdd || modalEdit || alertMessage ? 'blur-sm' : ''">
            <!-- sidebar-->
            <?php include 'templates/sidebar.php'; ?>

            <!-- Content -->
            <div class="w-full duration-500" x-bind:class="open ? 'ml-64' : 'ml-24'" x-cloak>
                <!-- Header -->
                <?php include 'templates/header.php'; ?>
                <div class="px-10 w-full mt-10">
                    <div class="flex flex-col">
                        <div class="flex items-center justify-end">
                            <span class="text-xl font-bold">TEACHERS INFORMATION</span>
                            <button class="flex items-center justify-center gap-3 bg-Sdarkblue text-white w-32 py-2 ml-auto"
                                x-on:click="modalAdd = true, clearFormInput()">
                                <i class="fa-solid fa-plus"></i>
                                <span>TEACHERS</span>
                            </button>
                        </div>
                        <div class="rounded-md overflow-x-auto p-4 pb-5 overflow-x-scroll md:overflow-x-hidden shadow-pressDownDeep bg-gray-50 mt-5">
                            <!-- Search -->
                            <div class="flex items-center justify-between gap-4 mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex items-center bg-gray-50">
                                        <input type="text" class="border shadow-pressDownDeep p-1 rounded-full pl-10 border  outline-none" id="search" x-model="searchTerm" @input="applyFilters" placeholder="Search...">
                                        <i class="fa-solid fa-magnifying-glass absolute top-1/2 -translate-y-1/2 left-4 text-slate-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <label for="perPage">Show:</label>
                                        <select x-model="perPage" @change="applyFilters()">
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                        </select>
                                    </div>
                                    <select x-model="statusFilter" @change="applyFilters()"
                                        class="shadow-pressDownDeep bg-gray-50 border py-2 px-4 focus:border-dashColor outline-none rounded-md">
                                        <option value="all">All</option>
                                        <option value="ABSENT">Absent</option>
                                        <option value="PRESENT">Present</option>
                                    </select>
                                </div>

                                <div class="flex gap-2"> <!--IMPORT & EXPORT BUTTONS -->
                                    <div class="relative flex items-center justify-center">
                                        <form id="importForm" action="databases/import-teachers.php" method="POST" enctype="multipart/form-data" x-on:submit.prevent="confirmImport">
                                            <input type="file" name="import_file" id="import_file" class="w-60 shadow-pressDownDeep bg-gray-200" required>
                                            <button type="submit" class="w-28 py-2 bg-Sdarkblue hover:bg-Sblue duration-500 text-white font-semibold text-sm py-2 px-4 shadow-lg rounded">
                                                <i class="fa-solid fa-file-import"></i>
                                                <span>Import</span>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="relative">
                                    <button class="flex gap-2 items-center justify-center w-28 py-2 bg-orange-600 hover:bg-orange-500 duration-500 text-white font-semibold text-sm py-2 px-4 shadow-lg rounded" onclick="exportToExcel()">
                                        <i class="fa-solid fa-cloud-arrow-down"></i>
                                        <span>Export</span>
                                    </button>
                                </div>
                                </div>
                                
                            </div>
                            <table class="w-full min-w-[800px]" id="teacherTable">
                                <thead class="text-black text-xs border-b border-gray-300">
                                    <tr>
                                        <th class="p-2">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                                :checked="paginatedList.length > 0 && paginatedList.every(t => selectedTeachers.includes(t.teacher_id)">
                                        </th>
                                        <th x-show="false" class="p-2 text-xs">CARD NO.</th>
                                        <th class="p-2 text-xs">PROFILE</th>
                                        <th class="p-2 text-xs">TEACHER ID</th>
                                        <th class="p-2 text-xs cursor-pointer relative group" @click="setSort('lastname')">
                                            <span>LAST NAME</span>
                                            <i class="fa-solid opacity-0 group-hover:opacity-100 ml-1 transition-opacity"
                                                :class="{
                        'fa-arrow-down': sortBy !== 'lastname' || (sortBy === 'lastname' && sortAsc), 
                        'fa-arrow-up': sortBy === 'lastname' && !sortAsc
                    }"></i>
                                        </th>
                                        <th class="p-2 text-xs cursor-pointer relative group" @click="setSort('firstname')">
                                            <span>FIRST NAME</span>
                                            <i class="fa-solid opacity-0 group-hover:opacity-100 ml-1 transition-opacity"
                                                :class="{
                        'fa-arrow-down': sortBy !== 'firstname' || (sortBy === 'firstname' && sortAsc), 
                        'fa-arrow-up': sortBy === 'firstname' && !sortAsc
                    }"></i>
                                        </th>
                                        <th class="p-2 text-xs">SECTION & YEAR LEVEL</th>
                                        <th class="p-2 text-xs">EMAIL</th>
                                        <th class="p-2 text-xs">SOCIAL</th>
                                        <th class="p-2 text-xs">MOBILE NO.</th>
                                        <th class="p-2 text-xs">STATUS</th>
                                        <th class="p-2 text-xs">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody class="md:text-[0.8rem] sm:text-[0.6rem] text-sm">
                                    <template x-if="paginatedList.length === 0">
                                        <tr class="border-b border-gray-300 text-center">
                                            <td colspan="11" class="p-3 text-gray-500">No data available</td>
                                        </tr>
                                    </template>
                                    <template x-for="teacher in paginatedList" :key="teacher.card_id">
                                        <tr class="border-b border-gray-300"
                                            :class="{ 'text-gray-500': teacher.is_hidden === '1' }">
                                            <td class="px-2 py-3 text-center">
                                                <input type="checkbox" @click="toggleSelection(teacher.teacher_id)"
                                                    :checked="selectedTeachers.includes(teacher.teacher_id)">
                                            </td>
                                            <td class="p-2 text-center" x-show="false" x-text="teacher.card_id"></td>
                                            <td class="p-2">
                                                <div class="w-12 h-12 mx-auto overflow-hidden">
                                                    <img :src="teacher.profile" alt="Profile" class="w-full h-full object-cover">
                                                </div>
                                            </td>

                                            <td class="p-2 text-center" x-text="teacher.teacher_id"></td>
                                            <td class="p-2 text-center" x-text="teacher.lastname"></td>
                                            <td class="p-2 text-center" x-text="teacher.firstname"></td>
                                            <td class="p-2 text-center" x-text="teacher.year_level"></td>
                                            <td class="p-2 text-center" x-text="teacher.email"></td>
                                            <td class="p-2 text-center" x-text="teacher.social"></td>
                                            <td class="p-2 text-center" x-text="teacher.mobile"></td>
                                            <td class="p-2 text-center ">
                                                <span class="font-semibold" x-text="teacher.status"
                                                    :class="{
                                    'text-green-600 bg-green-200 rounded-full py-1 px-2 ': teacher.status === 'PRESENT',
                                    'text-red-600 bg-red-200 rounded-full py-1 px-2 ': teacher.status === 'ABSENT'
                              }"></span>
                                            </td>
                                            <td class="p-2  relative" x-data="{ open: false }">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button
                                                        class="p-1 text-gray-600 transition-colors"
                                                        :class="{
                                                        'hover:text-blue-600': teacher.is_hidden !== '1',
                                                        'cursor-not-allowed opacity-50': teacher.is_hidden === '1'
                                                    }"
                                                        @click="
                                                        if (teacher.is_hidden !== '1') {
                                                            modalEdit = true;
                                                            teacher_id = teacher.teacher_id;
                                                            teacher_id = teacher.teacher_id;
                                                            lastname = teacher.lastname;
                                                            firstname = teacher.firstname;
                                                            profile = teacher.profile;
                                                            email = teacher.email;
                                                            social = teacher.social;
                                                            mobile = teacher.mobile;
                                                        }
                                                    ">
                                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                    </button>


                                                    <button
                                                        class="p-1 text-gray-600 transition-colors"
                                                        :class="{
                                                            'hover:text-red-600': teacher.is_hidden !== '1',
                                                            'cursor-not-allowed opacity-50': teacher.is_hidden === '1'
                                                        }"
                                                        @click.prevent="
                                                            if (teacher.is_hidden !== '1') {
                                                                deleteTeacher(teacher);
                                                            }
                                                        ">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>

                                                    <div class="relative" x-data="{ open: false }">
                                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer text-gray-700" @click="open = !open"></i>
                                                        <div x-show="open" @click.away="open = false"
                                                            class="absolute right-full mr-2 top-1/2 -translate-y-1/2 w-32 bg-white border border-gray-200 rounded shadow-md z-20">
                                                            <ul class="text-sm text-gray-700">
                                                                <template x-for="status in ['PRESENT', 'ABSENT']" :key="status">
                                                                    <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                                                        @click="updateStatus(teacher.teacher_id, status); open = false"
                                                                        x-text="status"></li>
                                                                </template>
                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                            <!--Pagination-->
                            <div class="flex justify-between items-center p-2 mt-4">
                                <!-- Showing Items Text -->
                                <span class="text-sm text-gray-600"
                                    x-text="'Showing ' + (paginatedList.length === 0 ? 0 : ((currentPage - 1) * perPage + 1)) + 
                                ' to ' + (paginatedList.length === 0 ? 0 : Math.min(currentPage * perPage, teacherList.length)) + 
                                ' of ' + teacherList.length + ' entries'">
                                </span>

                                <div class="flex items-center gap-3">
                                    <!-- Delete Button -->
                                    <div
                                        x-show="selectedTeachers.length > 0 && paginatedList.length > 0"
                                        class="flex items-center gap-3">
                                        <span x-text="selectedTeachers.length + ' record(s) selected'"></span>
                                        <button class="bg-red-600 text-white px-2 py-1 rounded" x-on:click="deleteSelectedTeachers()">
                                            Delete Selected
                                        </button>
                                    </div>


                                    <!-- Pagination Controls -->
                                    <div x-data="paginationComponent" class="flex items-center space-x-1 text-sm">
                                        <button @click="changePage(currentPage - 1)"
                                            :disabled="currentPage === 1"
                                            class="px-2 py-1 rounded border bg-gray-100 text-gray-500 disabled:opacity-50">
                                            &lt;
                                        </button>

                                        <template x-for="page in getPaginationRange()" :key="page">
                                            <button @click="changePage(page)"
                                                class="px-3 py-1 rounded border transition"
                                                :class="{'bg-Sdarkblue text-white font-bold': currentPage === page, 'bg-white text-gray-700': currentPage !== page}"
                                                x-text="page"></button>
                                        </template>

                                        <button @click="changePage(currentPage + 1)"
                                            :disabled="currentPage === totalPages"
                                            class="px-2 py-1 rounded border bg-gray-100 text-gray-500 disabled:opacity-50">
                                            &gt;
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blocker for modalShow -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10"
            x-show="modalAdd || modalEdit || alertMessage"
            x-transition.opacity>
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

            <div class="flex flex-col items-center gap-3 p-10 w-96 h-64 rounded-xl shadow-lg bg-white text-green-600"
                :class="alertType === 'success' ? 'text-green-600' : 'text-red-600'">

                <!-- Font Awesome Check Icon and Success Text -->
                <template x-if="alertType === 'success'">
                    <div class="flex flex-col items-center gap-2 ">
                        <i class="fa-regular fa-circle-check text-6xl animate-bounce"></i>
                        <span class="font-bold text-4xl">Success</span>
                    </div>
                </template>

                <!-- Alert Message -->
                <span class="text-lg text-center font-semibold" x-text="alertMessage"></span>
            </div>
        </div>

        <!-- Add Teacher Modal -->
        <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[35%] max-h-screen bg-white shadow-btnShadow z-50"
            x-show="modalAdd"
            x-on:click.outside="modalAdd = false; clearFormInput(); clearFormErrMsg()"
            x-cloak>

            <!-- Modal Header -->
            <div class="flex px-14 pt-10 items-center text-black">
                <h1 class="font-bold text-2xl mb-4">ADD NEW TEACHER</h1>
            </div>

            <!-- Scrollable Content -->
            <div class="relative px-14 overflow-y-auto" style="max-height: calc(100vh - 120px); padding-bottom: 2.5rem;">
                <form @submit.prevent="submitTeacherForm" x-ref="teacherAddForm">

                    <!-- Card ID -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="card_id" class="font-semibold">Card ID</label>
                        <input type="text" name="card_id" placeholder="Enter Card ID"
                            x-model="card_id"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="cardErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'" required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in cardErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>
                    <!-- Teacher ID -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="teacher_id" class="font-semibold">TEACHER_ID</label>
                        <input type="text" name="teacher_id" placeholder="Enter Teacher ID"
                            x-model="teacher_id"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="teacherErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'" required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in teacherErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- Last Name -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="lastname" class="font-semibold">Last Name</label>
                        <input type="text" name="lastname" placeholder="Enter Last Name"
                            x-model="lastname"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="lastnameErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'" required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in lastnameErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- First Name -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="firstname" class="font-semibold">First Name</label>
                        <input type="text" name="firstname" placeholder="Enter First Name"
                            x-model="firstname"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="firstnameErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'" required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in firstnameErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- Year Level-->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="year_level" class="font-semibold">Section & YearLevel</label>
                        <input type="text" name="year_level" placeholder="ex: Rizal-10"
                            x-model="year_level"

                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none" required>
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="email" class="font-semibold">Email</label>
                        <input type="text" name="email" placeholder="ex: Ww@gmail.com"
                            x-model="email"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="emailErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'">
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in emailErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- Year Level-->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="social" class="font-semibold">Social</label>
                        <input type="text" name="social" placeholder="ex: Facebook account or Messenger"
                            x-model="social"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none">
                    </div>
                    <!-- Mobile -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="mobile" class="font-semibold">Mobile No.</label>
                        <input type="text" name="mobile" placeholder="ex: 09223344556"
                            x-model="mobile"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="mobileErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'">
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in mobileErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- Profile Picture -->
                    <div class="flex flex-col gap-2 mt-3">
                        <label for="profile" class="font-semibold">Profile Picture</label>
                        <input type="file" name="profile" id="profile"
                            accept=".jpeg,.jpg,.png"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none mt-2" required>
                    </div>


                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 mt-6 mb-5">
                        <button type="button"
                            class="w-32 text-white bg-red-600 py-2 hover:bg-red-500"
                            x-on:click="modalAdd = false; clearFormInput(); clearFormErrMsg()">
                            Cancel
                        </button>
                        <button type="submit"
                            class="w-32 py-2 bg-hyaGreen text-white hover:bg-blue-700">
                            Add
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- edit Teacher Modal -->
        <div class="fixed py-10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pb-10 w-[45%] h-79 bg-white shadow-btnShadow z-50"
            x-show="modalEdit"
            x-on:click.outside="modalEdit = false; clearFormInput(); clearFormErrMsg()"
            x-cloak>

            <div class="flex px-14 items-center text-black p-2 ">
                <h1 class="font-bold text-2xl mb-2">EDIT TEACHER</h1>
            </div>

            <div class="relative px-14  overflow-y-auto" style="max-height: calc(100vh - 120px); padding-bottom: 2.5rem;">
                <form @submit.prevent="submitEditForm" x-ref="teacherEditForm">
                    <input type="hidden" x-model="teacher_id" name="teacher_id">
                    <!-- Add this below -->
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in ojtErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>
                    <div class="flex flex-col justify-between gap-2 mt-2">
                        <label for="teacher_id" class="font-semibold">Teacher ID</label>
                        <input type="text"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="teacherErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'"
                            name="teacher_id" placeholder="Enter Teacher ID" x-model="teacher_id"
                            @keyup="validateForm()" required>
                    </div>

                    <!-- Add this below -->
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in teacherErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <div class="flex flex-col justify-between gap-2 mt-2">
                        <label for="lastname" class="font-semibold">Last name</label>
                        <input type="text"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="lastnameErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'"
                            name="lastname" placeholder="Enter Last name" x-model="lastname"
                            @keyup="validateForm()" required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in lastnameErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <div class="flex flex-col justify-between gap-2 mt-2">
                        <label for="firstname" class="font-semibold">First name</label>
                        <input type="text"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="firstnameErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'"
                            name="firstname" placeholder="Enter First name" x-model="firstname"
                            @keyup="validateForm()" required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in firstnameErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col justify-between gap-2 mt-2">
                        <label for="email" class="font-semibold">Email</label>
                        <input type="text"
                            name="email"
                            placeholder="ex: Ww@gmail.com"
                            x-model="email"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="emailErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'"
                            required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in emailErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>
                    <div class="flex flex-col justify-between gap-2 mt-2">
                        <label for="social" class="font-semibold">Social</label>
                        <input type="text"
                            name="social"
                            placeholder=""
                            x-model="social" @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="emailErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'"
                            required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in socialErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <div class="flex flex-col justify-between gap-2 mt-2">
                        <label for="mobile" class="font-semibold">Mobile</label>
                        <input type="text"
                            name="mobile"
                            placeholder=""
                            x-model="mobile" @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="emailErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'"
                            required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in mobileErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <div class="flex flex-col justify-between gap-2 mt-3">
                        <label for="profile" class="font-semibold">Profile Picture</label>

                        <!-- Preview current profile -->
                        <template x-if="profile">
                            <img :src="profile" alt="Current Profile" class="w-32 h-32 object-cover mb-2 border-2 rounded">
                        </template>

                        <input type="file" class="pl-2 p-2 border-2 shadow-inputShadow outline-none mt-2" name="profile" x-model="profile" id="profile" accept=".jpeg,.jpg,.png">
                    </div>



                    <div class="flex items-center justify-end gap-3 mt-5">
                        <button type="button" class="w-32 text-white bg-red-600 py-2 hover:bg-red-500"
                            x-on:click="modalEdit = false; clearFormInput(); clearFormErrMsg()">
                            Cancel
                        </button>

                        <button type="submit" class="w-32 py-2 bg-hyaGreen text-white hover:bg-blue-700">
                            Add
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function createTempTable() {
                const originalTable = document.getElementById("teacherTable");
                const tempTable = document.createElement("table");


                // Create table header
                const thead = document.createElement("thead");
                const originalHeadRow = originalTable.querySelector("thead tr");
                const tempHeadRow = originalHeadRow.cloneNode(false);

                // Copy only valid columns (excluding checkbox and actions)
                const headCells = Array.from(originalHeadRow.cells);
                headCells.forEach((cell, index) => {
                    if (index !== 0 && cell.textContent.trim() !== "Actions") {
                        const newCell = cell.cloneNode(true);
                        tempHeadRow.appendChild(newCell);
                    }
                });
                thead.appendChild(tempHeadRow);
                tempTable.appendChild(thead);

                // Create table body
                const tbody = document.createElement("tbody");
                const rows = originalTable.querySelectorAll("tbody tr");
                rows.forEach(row => {
                    const tempRow = row.cloneNode(false);
                    const cells = Array.from(row.cells);

                    cells.forEach((cell, index) => {
                        // Exclude checkbox and actions columns
                        if (index !== 0 && index !== cells.length - 1) {
                            const newCell = document.createElement("td");

                            // Check if the cell is in the "Time / Date Created" column (index 7)
                            if (index === 7) {
                                const rawDate = cell.textContent.trim();
                                if (rawDate !== "N/A" && !isNaN(Date.parse(rawDate))) {
                                    // Convert the raw date string to a Date object, then format it
                                    const formattedDate = new Date(rawDate).toLocaleString('en-US', {
                                        hour12: true
                                    });
                                    newCell.textContent = formattedDate; // Set formatted date as text
                                } else {
                                    newCell.textContent = rawDate; // Leave "N/A" or other non-date values as is
                                }
                            } else {
                                // Copy the text of other columns (e.g., Status) directly
                                newCell.textContent = cell.textContent.trim();
                            }

                            tempRow.appendChild(newCell);
                        }
                    });
                    tbody.appendChild(tempRow);
                });
                tempTable.appendChild(tbody);

                return tempTable;
            }

            function exportToExcel() {
                // Check if teacher data is available
                if (!window.teacherList || window.teacherList.length === 0) {
                    console.log(window.teacherList);
                    alert("No data available to export.");
                    return;
                }

                // Convert teacher data into a format suitable for Excel
                const dataForExport = window.teacherList.map(teacher => ({
                    "Teacher ID": teacher.teacher_id,
                    "Last Name": teacher.lastname,
                    "First Name": teacher.firstname,
                    "Email": teacher.email,
                    "Social": teacher.social,
                    "Mobile": teacher.mobile,
                    "Status": teacher.status,
                    "Date": new Date(teacher.updtime).toLocaleDateString('en-US')
                }));

                // Create a worksheet from JSON data
                const worksheet = XLSX.utils.json_to_sheet(dataForExport);

                // Create a new workbook and add the worksheet
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Teacher List");

                // Set column widths to ensure proper formatting
                worksheet["!cols"] = [{
                        wch: 15
                    }, // Teacher ID
                    {
                        wch: 20
                    }, // Last Name
                    {
                        wch: 20
                    }, // First Name
                    {
                        wch: 15
                    }, // Email
                    {
                        wch: 15
                    }, // Social
                    {
                        wch: 15
                    }, // Mobile
                    {
                        wch: 15
                    }, // Status
                    {
                        wch: 25
                    } // Date
                ];

                // Download the Excel file
                XLSX.writeFile(workbook, "TeacherList.xlsx");
            }
        </script>

</body>

</html>