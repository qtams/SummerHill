<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student</title>
    <?php include "styles/styles.php" ?>

</head>

<body x-data="{
        modalAdd: false,
        modalEdit: false,
        studentList: [],
        paginatedList: [],
        filteredList: [], // Initialize filteredList
        perPage: 10,
        currentPage: 1,
        totalPages: 1,
        searchTerm: '',  // No storage, resets on page refresh/change
        student_id: '',
        lastname: '',
        firstname: '',
        profile:'',
        year_level:'',
        mobile:'',
        social:'',
        email:'',
        card_id:'',
        year_level: '',
        other_year_level: '',
        section: '',
        other_section: '',
        selectedYearLevel: '',
        selectedSection: '',

        profile_pic:'',
        lastnameErrMsg:[],
        firstnameErrMsg:[],
        studentErrMsg:[],
        cardErrMsg:[],
        emailErrMsg: [],
        mobileErrMsg: [],
        errMsg: '',
        hasError: false,
        alertMessage: '',
        alertType: '',
        selectedStudents: [], // Initialize selectedStudents
        selectAll: false,
        sortBy: '',
        sortAsc: true,
        statusFilter: 'all',
        sectionList: [],
        profile_pic: '',       // current image from DB
        previewUrl: '',        // new image preview

        
        getStudent() {
            fetch('get-table-data.php?get_data=student')
                .then(response => response.json())
                .then(data => {
                    // Store the current page before applying filters
                    const currentPageBeforeRefresh = this.currentPage;
                    
                    this.studentList = data;
                    console.log('patient List:', this.studentList);
                    
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
                    window.studentList = this.studentList;
                })
                .catch(error => console.error('Error fetching trainee:', error));
        },

        getSections() {
            fetch('get-table-data.php?get_data=sections')
                .then(res => res.json())
                .then(data => {
                    this.sectionList = data;
                })
                .catch(error => console.error('Error loading sections:', error));
        },

        init() {
            // Fetch initial data
            this.getStudent();
            this.getSections();
           

            // Set interval to refresh student and school data every 10 seconds
            setInterval(() => {
                this.getStudent();
                this.getSections();
               
            }, 3000);  
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

        showAlert(message, type = 'success') {
            this.alertMessage = message;
            this.alertType = type;
            setTimeout(() => this.alertMessage = '', 3000); // Hide alert after 3 seconds
        },

        submitStudentForm(event) {
            event.preventDefault();
            this.validateForm();

            if (this.hasError) {
                console.log('Form has errors, submission stopped!');
                return;
            }
            console.log('card ID:', this.card_id);
            console.log('student ID:', this.student_id);
            console.log('Last Name:', this.lastname);
            console.log('First Name:', this.firstname);

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

            fetch('databases/add-students.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.alertMessage = data.message;
                this.alertType = data.status;

                if (data.status === 'success') {
                    this.modalAdd = false;
                    this.getStudent();
                    this.getSections();
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


        submitEditForm(event) {
            event.preventDefault();
            this.validateForm();

            if (this.hasError) {
                console.log('Form has errors, submission stopped!');
                return;
            }

            const formData = new FormData(event.target);

            fetch('databases/edit-students.php', {
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
                    this.getStudent();
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


        validateForm() {
            this.lastnameErrMsg = [];
            this.firstnameErrMsg = [];
            this.mobileErrMsg=[];
            this.emailErrMsg=[];
            this.studentErrMsg = [];
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
            // Student ID validation
            if (this.mobile.trim() !== '' && !/^[0-9]+$/.test(this.mobile)) {
                this.hasError = true;
                this.mobileErrMsg.push('*Mobile Number can only contain numbers.');
            }
             // Student ID validation
            if (this.email.trim() !== '' && !/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(this.email)) {
                this.hasError = true;
                this.emailErrMsg.push('*email contains @gmail.com at the end.');
            }

            // Student ID validation
            if (this.student_id.trim() !== '' && !/^[0-9]+$/.test(this.student_id)) {
                this.hasError = true;
                this.studentErrMsg.push('*Student ID can only contain numbers.');
            }
            // Student ID validation
            if (this.card_id.trim() !== '' && !/^[0-9]+$/.test(this.card_id)) {
                this.hasError = true;
                this.cardErrMsg.push('*Card ID can only contain numbers.');
            }
            
            

        },

        deleteStudent(student) {
            if (!confirm(`Are you sure you want to delete ${student.firstname} ${student.lastname}?\nAll related time monitoring records will also be deleted.`)) {
                return;
            }

            fetch('databases/delete-student.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ student_id: student.student_id }),
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.studentList = this.studentList.filter(s => s.student_id !== student.student_id);
                    this.applyFilters();
                    this.alertMessage = 'Student and related records deleted successfully!';
                    this.alertType = 'success';
                } else {
                    this.alertMessage = result.message || 'Failed to delete Student.';
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

        applyFilters(resetPage = true) {
            let filteredList = this.studentList;

            // Filter by search term across all specified fields
            if (this.searchTerm) {
                const term = this.searchTerm.toLowerCase();
                filteredList = filteredList.filter(student =>
                    (student.student_id && student.student_id.toString().toLowerCase().includes(term)) ||
                    (student.firstname && student.firstname.toLowerCase().includes(term)) ||
                    (student.lastname && student.lastname.toLowerCase().includes(term)) ||
                    (student.year_level && student.year_level.toLowerCase().includes(term)) 
                    
                );
            }

            // Filter by status
            if (this.statusFilter && this.statusFilter !== 'all') {
                filteredList = filteredList.filter(student => 
                    student.status === this.statusFilter
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
            window.filteredList = this.filteredList;
            this.paginate();
        },

        paginate() {
            this.perPage = Number(this.perPage); // Ensure it's always a number

            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;

            console.log('DEBUG - Current Page:', this.currentPage);
            console.log('DEBUG - Per Page:', this.perPage, typeof this.perPage);
            console.log('DEBUG - Start Index:', start, 'End Index:', end);

            this.paginatedList = (this.filteredList || this.studentList).slice(start, end);
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

        formatDate(dateString) {
            if (!dateString || dateString === '0000-00-00') {
                return 'N/A';
            }

            const date = new Date(dateString);
            if (isNaN(date)) {
                return 'N/A';
            }

            const options = {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
            };

            // This already includes everything in the correct format
            return date.toLocaleString('en-US', options).replace(',', ' at');
        },

        clearFormErrMsg() {
            this.hasError = false;
            this.lastnameErrMsg = [];
            this.firstnameErrMsg = [];
            this.studentErrMsg = [];
            this.mobileErrMsg = [];
            this.emailErrMsg = [];
            this.cardErrMsg = [];
        },

        clearFormInput() {
            this.lastname = '';
            this.firstname = '';
            this.student_id = '';
            this.profile = '';
            this.year_level = '';
            this.other_year_level = '';
            this.section = '';
            this.social = '';
            this.other_section = '';
            this.selectedYearLevel = '';
            this.selectedSection = '';
            this.email = '';
            this.mobile = '';
            this.card_id = '';
             this.previewUrl = '';

            const fileInput = this.$refs.studentAddForm.querySelector('input[name=\'profile\']');
            if (fileInput) fileInput.value = '';
        },

        toggleAll() {
            if (this.selectAll) {
                // Use the already filtered list from applyFilters()
                let filteredStudentIds = this.filteredList.map(student => student.student_id);
                
                // Merge selected student, avoiding duplicates
                this.selectedStudents = [...new Set([...this.selectedStudents, ...filteredStudentIds])];
            } else {
                // Deselect only filtered student, keep selections from other pages
                let filteredStudentIds = this.filteredList.map(student => student.student_id);
                
                this.selectedStudents = this.selectedStudents.filter(id => !filteredStudentIds.includes(id));
            }
        },

        toggleSelection(student_id) {
            if (this.selectedStudents.includes(student_id)) {
                this.selectedStudents = this.selectedStudents.filter(id => id !== student_id);
            } else {
                this.selectedStudents.push(student_id);
            }
        },

        deleteSelectedStudents() {
            if (this.selectedStudents.length === 0) {
            console.warn('No Trainee selected for deletion.');
            return;
            }

            console.log('Sending student_id:', JSON.stringify(this.selectedStudents));

            if (!confirm(`Are you sure you want to delete ${this.selectedStudents.length} student(s)?`)) return;

            fetch('databases/deleted-selected-students.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `student_id=${encodeURIComponent(JSON.stringify(this.selectedStudents))}`
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
                    this.studentList = this.studentList.filter(s => !this.selectedStudents.includes(s.student_id));
                    this.selectedStudents = [];
                    this.applyFilters();
                    this.selectAll = false;
                    this.showAlert('student(s) deleted successfully.', 'success', 'text-xs');
                } else {
                    this.showAlert(data.message || 'Failed to delete selected student(s).', 'error');
                }
            })
            .catch(error => {
                console.error('Bulk Delete Error:', error);
                this.showAlert('An error occurred while deleting.', 'error');
            });
        },

        formatHours(decimalHours) {
            if (decimalHours === null || decimalHours === undefined) return '0 hrs';
            
            const hours = Math.floor(decimalHours);
            const minutes = Math.round((decimalHours % 1) * 60);
            
            let result = '';
            
            if (hours > 0) {
                result += hours === 1 ? `${hours} hr` : `${hours} hrs`;
            }
            
            if (minutes > 0) {
                if (hours > 0) result += ' and ';
                result += minutes === 1 ? `${minutes} min` : `${minutes} mins`;
            }
            
            // If both hours and minutes are 0
            if (result === '') {
                return '0 hrs';
            }
            
            return result;
        },

         confirmImport() {
            if (confirm('Are you sure you want to upload this file?')) {
                const formData = new FormData(document.getElementById('importForm'));
                fetch('databases/import-students.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message); // Success message

                        // Check if there are errors to display
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

                    document.getElementById('import_file').value = ''; // Clear file input
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during the file upload.');
                    document.getElementById('import_file').value = ''; // Clear file input
                });
            } else {
                alert('File upload canceled.');
            }
        },

       

    }" x-init="getStudent(); getSections()" x-cloak>

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
                            <span class="text-xl font-bold">STUDENTS INFORMATION</span>
                            <button class="flex items-center justify-center gap-3 bg-Sdarkblue text-white w-32 py-2 ml-auto"
                                x-on:click="modalAdd = true, clearFormInput()">
                                <i class="fa-solid fa-plus"></i>
                                <span>STUDENTS</span>
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
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="30">30</option>
                                        </select>
                                    </div>
                                    <select x-model="statusFilter" @change="applyFilters()"
                                        class="shadow-pressDownDeep bg-gray-50 border py-2 px-4 focus:border-dashColor outline-none rounded-md">
                                        <option value="all">All</option>
                                        <option value="Evaluating">Absent</option>
                                        <option value="On Going">Present</option>
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <div class="relative flex items-center justify-center">
                                        <form id="importForm" action="databases/import-students.php" method="POST" enctype="multipart/form-data" x-on:submit.prevent="confirmImport">
                                            <input type="file" name="import_file" id="import_file" class="w-60 shadow-pressDownDeep bg-gray-200" required>
                                            <button type="submit" class="w-28 py-2 bg-Sdarkblue hover:bg-Sblue duration-500 text-white font-semibold text-sm py-2 px-4 shadow-lg rounded">
                                                <i class="fa-solid fa-file-import"></i>
                                                <span>Import</span>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="relative">
                                        <button class="flex gap-2 items-center justify-center w-28 py-2 bg-Sdarkblue hover:bg-Sblue duration-500 text-white font-semibold text-sm py-2 px-4 shadow-lg rounded" onclick="exportToExcel()">
                                            <i class="fa-solid fa-cloud-arrow-down"></i>
                                            <span>Export</span>
                                        </button>
                                    </div>
                                </div>
                            </div>


                            <table class="w-full min-w-[800px]" id="studentTable">
                                <thead class="text-black text-xs border-b border-gray-300">
                                    <tr>
                                        <th class="p-2">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                                :checked="paginatedList.length > 0 && paginatedList.every(t = selectedStudents.includes(s.student_id)">
                                        </th>
                                        <th class="p-2 text-xs">CARD NO.</th>
                                        <th class="p-2 text-xs">PROFILE</th>
                                        <th class="p-2 text-xs">STUDENT NO.</th>
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
                                        <th class="p-2 text-xs">SECTION</th>
                                        <th class="p-2 text-xs">YEAR LEVEL</th>
                                        <th class="p-2 text-xs">EMAIL</th>
                                        <th class="p-2 text-xs">SOCIAL</th>
                                        <th class="p-2 text-xs">MOBILE NO.</th>
                                        <th class="p-2 text-xs">ACTIONS</th>
                                    </tr>
                                </thead>

                                <tbody class="md:text-[0.8rem] sm:text-[0.6rem] text-sm">
                                    <template x-if="paginatedList.length === 0">
                                        <tr class="border-b border-gray-300 text-center">
                                            <td colspan="11" class="p-3 text-gray-500">No data available</td>
                                        </tr>
                                    </template>

                                    <template x-for="student in paginatedList" :key="student.student_id">
                                        <tr class="border-b border-gray-300"
                                            :class="{ 'text-gray-500': student.is_hidden === '1' }">
                                            <td class="px-2 py-3 text-center">
                                                <input type="checkbox" @click="toggleSelection(student.student_id)"
                                                    :checked="selectedStudents.includes(student.student_id)">
                                            </td>
                                            <td class="p-2 text-center" x-text="student.card_id || 'N/A'"></td>
                                            <td class="p-2">
                                                <div class="w-12 h-12 mx-auto overflow-hidden  border">
                                                    <img :src="student.profile ? student.profile : 'images/profile/noImage.jpg'"
                                                        alt="Profile"
                                                        class="w-full h-full object-cover">
                                                </div>
                                            </td>


                                            <td class="p-2 text-center" x-text="student.student_id"></td>
                                            <td class="p-2 text-center" x-text="student.lastname"></td>
                                            <td class="p-2 text-center" x-text="student.firstname"></td>
                                            <td class="p-2 text-center" x-text="student.section"></td>
                                            <td class="p-2 text-center" x-text="student.year_level"></td>
                                            <td class="p-2 text-center" x-text="student.email || 'N/A'"></td>
                                            <td class="p-2 text-center" x-text="student.social || 'N/A'"></td>
                                            <td class="p-2 text-center" x-text="student.mobile || 'N/A'"></td>

                                            <td class="p-2  relative" x-data="{ open: false }">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button
                                                        class="p-1 text-gray-600 transition-colors"
                                                        :class="{
                                                        'hover:text-blue-600': student.is_hidden !== '1',
                                                        'cursor-not-allowed opacity-50': student.is_hidden === '1'
                                                    }"
                                                        @click="
                                                        if (student.is_hidden !== '1') {
                                                            modalEdit = true;
                                                            student_id = student.student_id;
                                                            card_id = student.card_id;
                                                            lastname = student.lastname;
                                                            firstname = student.firstname;
                                                            section = student.section;
                                                            year_level = student.year_level;
                                                            email = student.email;
                                                            mobile = student.mobile;
                                                            social = student.social;
                                                            profile = student.profile;
                                                            
                                                        }
                                                    ">
                                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                    </button>


                                                    <button
                                                        class="p-1 text-gray-600 transition-colors"
                                                        :class="{
                                                            'hover:text-red-600': student.is_hidden !== '1',
                                                            'cursor-not-allowed opacity-50': student.is_hidden === '1'
                                                        }"
                                                        @click.prevent="
                                                            if (student.is_hidden !== '1') {
                                                                deleteStudent(student);
                                                            }
                                                        ">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>

                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>


                            <!-- Pagination -->
                            <div class="flex justify-between items-center p-2 mt-4">
                                <!-- Showing Items Text -->
                                <span class="text-sm text-gray-600"
                                    x-text="'Showing ' + (paginatedList.length === 0 ? 0 : ((currentPage - 1) * perPage + 1)) + 
                                ' to ' + (paginatedList.length === 0 ? 0 : Math.min(currentPage * perPage, studentList.length)) + 
                                ' of ' + studentList.length + ' entries'">
                                </span>

                                <div class="flex items-center gap-3">
                                    <!-- Delete Button -->
                                    <div
                                        x-show="selectedStudents.length > 0 && paginatedList.length > 0"
                                        class="flex items-center gap-3">
                                        <span x-text="selectedStudents.length + ' record(s) selected'"></span>
                                        <button class="bg-red-600 text-white px-2 py-1 rounded" x-on:click="deleteSelectedStudents()">
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


        <!-- Blocker for modalShow -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10"
            x-show="modalAdd || modalEdit || alertMessage">
        </div>

        <!-- Add Trainee Modal -->
        <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[35%] max-h-screen bg-white shadow-btnShadow z-50"
            x-show="modalAdd"
            x-on:click.outside="modalAdd = false; clearFormInput(); clearFormErrMsg()"
            x-cloak>

            <!-- Modal Header -->
            <div class="flex px-14 pt-10 items-center text-black">
                <h1 class="font-bold text-2xl mb-4">ADD NEW STUDENTS</h1>
            </div>

            <!-- Scrollable Content -->
            <div class="relative px-14 overflow-y-auto" style="max-height: calc(100vh - 120px); padding-bottom: 2.5rem;">
                <form @submit.prevent="submitStudentForm" x-ref="studentAddForm">

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

                    <!-- Student ID -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="student_id" class="font-semibold">STUDENT ID</label>
                        <input type="text" name="student_id" placeholder="Enter Student ID"
                            x-model="student_id"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="studentErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'" required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in studentErrMsg" :key="error">
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

                    <!-- Section Selection -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="section" class="font-semibold">Section</label>

                        <select name="section"
                            x-model="selectedSection"
                            :required="selectedSection !== 'OTHER'"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none border-gray-300">
                            <option value="" disabled selected>Select section</option>

                            <template x-for="section in sectionList" :key="section">
                                <option :value="section" x-text="section"></option>
                            </template>

                            <option value="OTHER">Other</option>
                        </select>

                        <!-- Only show this input when 'OTHER' is selected -->
                        <input type="text"
                            name="other_section"
                            x-show="selectedSection === 'OTHER'"
                            x-model="other_section"
                            :required="selectedSection === 'OTHER'"
                            x-transition
                            placeholder="Enter Other Section"
                            class="mt-2 pl-2 p-2 border-2 shadow-inputShadow outline-none border-gray-300">
                    </div>


                    <!-- Course year level -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="year_level" class="font-semibold">Grade Level</label>
                        <select name="year_level"
                            x-model="selectedYearLevel"
                            required
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none border-gray-300">
                            <option value="" disabled selected>Select Level</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>

                        </select>
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="email" class="font-semibold">Email</label>
                        <input type="text" name="email" placeholder="ex: summerhill@gmail.com"
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

                        <!-- Live Preview -->
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Profile Preview" class="w-32 h-32 object-cover mb-2 border-2 rounded">
                        </template>

                        <input type="file" name="profile" id="profile"
                            accept=".jpeg,.jpg,.png"
                            @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : ''"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none mt-2">
                    </div>



                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 mt-6 mb-5">
                        <button type="button"
                            class="w-32 text-white bg-red-600 py-2 hover:bg-red-500"
                            x-on:click="modalAdd = false; clearFormInput(); clearFormErrMsg()">
                            Cancel
                        </button>
                        <button type="submit"
                            class="w-32 py-2 bg-Sdarkblue text-white hover:bg-Sblue">
                            Add
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Trainee Modal -->
        <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[35%] max-h-screen bg-white shadow-btnShadow z-50"
            x-show="modalEdit"
            x-on:click.outside="modalEdit = false; clearFormInput(); clearFormErrMsg()"
            x-cloak>

            <!-- Modal Header -->
            <div class="flex px-14 pt-10 items-center text-black">
                <h1 class="font-bold text-2xl mb-4">EDIT STUDENT</h1>
            </div>

            <!-- Scrollable Content -->
            <div class="relative px-14 overflow-y-auto" style="max-height: calc(100vh - 120px); padding-bottom: 2.5rem;">
                <form @submit.prevent="submitEditForm" x-ref="traineeEditForm">

                    <!-- Card ID -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="card_id" class="font-semibold">Card ID</label>
                        <input type="text" name="card_id" x-model="card_id"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="cardErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'" required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in cardErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- Student ID (hidden) -->
                    <input type="hidden" name="student_id" x-model="student_id">

                    <!-- Last Name -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="lastname" class="font-semibold">Last Name</label>
                        <input type="text" name="lastname" x-model="lastname"
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
                        <input type="text" name="firstname" x-model="firstname"
                            @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="firstnameErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'" required>
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in firstnameErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- Section Selection -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="section" class="font-semibold">Section</label>
                        <select name="section" x-model="section"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none border-gray-300">
                            <option value="" disabled>Select section</option>
                            <template x-for="section in sectionList" :key="section">
                                <option :value="section" x-text="section"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Course year level -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="year_level" class="font-semibold">Grade Level</label>
                        <select name="year_level"
                            x-model="year_level"
                            required
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none border-gray-300">
                            <option value="" disabled selected>Select Level</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>

                        </select>
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="email" class="font-semibold">Email</label>
                        <input type="text" name="email" placeholder="ex: summerhill@gmail.com"
                            x-model="email" @keyup="validateForm()"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                            :class="emailErrMsg.length > 0 ? 'border-red-500' : 'border-gray-300'">
                    </div>
                    <div class="mt-2 mb-2 text-red-600 text-md">
                        <template x-for="error in emailErrMsg" :key="error">
                            <p x-text="error"></p>
                        </template>
                    </div>

                    <!-- Social -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="social" class="font-semibold">Social</label>
                        <input type="text" name="social" placeholder="ex: Facebook or Messenger" x-model="social"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none">
                    </div>

                    <!-- Mobile -->
                    <div class="flex flex-col gap-2 mt-2">
                        <label for="mobile" class="font-semibold">Mobile No.</label>
                        <input type="text" name="mobile" placeholder="ex: 09223344556" x-model="mobile" @keyup="validateForm()"
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

                        <!-- Live preview if new file is selected -->
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="New Profile Preview"
                                class="w-32 h-32 object-cover mb-2 border-2 rounded">
                        </template>

                        <!-- Show existing profile or fallback to default -->
                        <template x-if="!previewUrl">
                            <img :src="profile || 'images/profile/noImage.jpg'"
                                alt="Current Profile"
                                class="w-32 h-32 object-cover mb-2 border-2 rounded">
                        </template>

                        <input type="file" name="profile" id="profile" accept=".jpeg,.jpg,.png"
                            @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : ''"
                            class="pl-2 p-2 border-2 shadow-inputShadow outline-none mt-2">
                    </div>


                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 mt-6 mb-5">
                        <button type="button" class="w-32 text-white bg-red-600 py-2 hover:bg-red-500"
                            x-on:click="modalEdit = false; clearFormInput(); clearFormErrMsg()">
                            Cancel
                        </button>
                        <button type="submit" class="w-32 py-2 bg-Sdarkblue text-white hover:bg-Sblue">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>


    </div>
    </div>

    <script>
        function createTempTable() {
            const originalTable = document.getElementById("studentTable");
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
            const filteredData = window.filteredList;

            if (!filteredData || filteredData.length === 0) {
                alert("No data available to export.");
                return;
            }

            // Convert student data into a format suitable for Excel
            const dataForExport = filteredData.map(student => ({
                "Student Card ID": student.card_id || 'N/A',
                "Student ID": student.student_id,
                "Last Name": student.lastname,
                "First Name": student.firstname,
                "Email": student.email || 'N/A',
                "Social": student.social || 'N/A',
                "Mobile": student.mobile || 'N/A',
                "Section": student.section,
                "Year Level": student.year_level
            }));

            // Create a worksheet from JSON data
            const worksheet = XLSX.utils.json_to_sheet(dataForExport);

            // Create a new workbook and add the worksheet
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Student List");

            // Set column widths to ensure proper formatting
            worksheet["!cols"] = [{
                    wch: 15
                }, // Student Card ID
                {
                    wch: 20
                }, // School Student ID
                {
                    wch: 20
                }, // Last Name
                {
                    wch: 20
                }, // First Name
                {
                    wch: 20
                }, // Email
                {
                    wch: 20
                }, // Social
                {
                    wch: 20
                }, // Mobile
                {
                    wch: 15
                }, // section
                {
                    wch: 25
                } // Year Level
            ];

            // Download the Excel file
            XLSX.writeFile(workbook, "Student List.xlsx");
        }
    </script>
</body>

</html>