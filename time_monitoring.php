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
        traineeIDList: [],
        paginatedList: [],
        filteredList: [], // Initialize filteredList
        perPage: 10,
        currentPage: 1,
        totalPages: 1,
        searchTerm: '',  // No storage, resets on page refresh/change
        trainee_id: '',
        student_number: '',
        lastname: '',
        firstname: '',
        profile:'',
        profile_pic:'',
        lastnameErrMsg:[],
        firstnameErrMsg:[],
        ojtErrMsg:[],
        studentErrMsg : [],
        errMsg: '',
        hasError: false,
        alertMessage: '',
        alertType: '',
        selectedTrainees: [], // Initialize selectedStudents
        selectAll: false,
        sortBy: '',
        sortAsc: true,
        time_in: '',
        time_out: '',
        minDateTime: '',
        time_id: '',
        startDate: '',
        endDate: '',
        filterPosition: 'all',


        init() {
            // Fetch initial data
            this.getTrainee();
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            // set min to today at 00:00
            this.minDateTime = `${year}-${month}-${day}T00:00`;

            // Set interval to refresh student and school data every 10 seconds
            setInterval(() => {
                this.getTrainee();
               
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

        submitTimeForm(event) {
            event.preventDefault();
            this.validateForm();

            if (this.hasError) {
                console.log('Form has errors, submission stopped!');
                return;
            }

            console.log('Trainee ID:', this.trainee_id);
            console.log('Student Number:', this.time_in);
            console.log('Last Name:', this.time_out);


            const formData = new FormData(event.target);

            fetch('databases/add-time.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.alertMessage = data.message;
                this.alertType = data.status;

                if (data.status === 'success') {
                    this.modalAdd = false;
                    this.getTrainee();
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
           
            if (this.hasError) {
                console.log('Form has errors, submission stopped!');
                return;
            }
           
            const formData = new FormData(event.target);

            fetch('databases/edit-time.php', {
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
                    this.getTrainee();
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
            this.ojtErrMsg = [];
            this.studentErrMsg = [];
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

            // Trainee ID validation
            if (this.trainee_id.trim() !== '' && !/^[0-9]+$/.test(this.trainee_id)) {
                this.hasError = true;
                this.ojtErrMsg.push('*Trainee ID can only contain numbers.');
            }

            // Student Number validation
            if (this.student_number.trim() !== '' && !/^[0-9]+$/.test(this.student_number)) {
                this.hasError = true;
                this.studentErrMsg.push('*Student Number can only contain numbers.');
            }
        },

        deleteTime(time) {
            if (!confirm(`Are you sure you want to delete ${time.firstname} ${time.lastname}?`)) {
                return;
            }

            fetch('databases/delete-time.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ time_id: time.time_id }), // ✅ Fixed here
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.studentList = this.studentList.filter(t => t.time_id !== time.time_id);
                    this.applyFilters();
                    this.alertMessage = 'Time deleted successfully!';
                    this.alertType = 'success';
                } else {
                    this.alertMessage = result.message || 'Failed to delete Trainee.';
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

        getTrainee() {
            fetch('get-table-data.php?get_data=time')
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

        applyFilters(resetPage = true) {
            let filteredList = this.studentList;

            // Filter by search term
            if (this.searchTerm) {
                const term = this.searchTerm.toLowerCase();
                filteredList = filteredList.filter(time =>
                    (time.student_number && time.student_number.toString().toLowerCase().includes(term)) ||
                    (time.firstname && time.firstname.toLowerCase().includes(term)) ||
                    (time.lastname && time.lastname.toLowerCase().includes(term)) 
                );
            }

            // Filter by date range (based on time_in)
            if (this.startDate || this.endDate) {
                const start = this.startDate ? new Date(this.startDate) : null;
                const end = this.endDate ? new Date(this.endDate) : null;

                if (start) start.setHours(0, 0, 0, 0);
                if (end) end.setHours(23, 59, 59, 999);

                filteredList = filteredList.filter(time => {
                    if (!time.time_in) return false;
                    const timeInDate = new Date(time.time_in);

                    let matches = true;
                    if (start) matches = matches && timeInDate >= start;
                    if (end) matches = matches && timeInDate <= end;
                    return matches;
                });
            }

            if (this.filterPosition === 'student') {
                filteredList = filteredList.filter(time => time.position === 'student');
            } else if (this.filterPosition === 'teacher') {
                filteredList = filteredList.filter(time => time.position === 'teacher');
            } else if (this.filterPosition === 'visitor') {
                filteredList = filteredList.filter(time => time.position === 'visitor');
            }


            if (resetPage) this.currentPage = 1;

            this.totalPages = Math.ceil(filteredList.length / this.perPage) || 1;
            this.filteredList = filteredList;
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

        formatDateTime(dateString) {
            // Return N/A for empty or invalid dates
            if (!dateString || dateString === '0000-00-00 00:00:00' || isNaN(new Date(dateString))) {
                return 'N/A';
            }
            
            const date = new Date(dateString);
            
            // Return N/A if the date is still invalid
            if (isNaN(date.getTime())) {
                return 'N/A';
            }
            
            // Format options for the date part
            const datePart = date.toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            
            // Format options for the time part
            const timePart = date.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            return `${datePart} at ${timePart}`;
        },

        formatHours(decimalHours) {
            // Return N/A for null/undefined or invalid numbers
            if (decimalHours === null || decimalHours === undefined || isNaN(decimalHours)) {
                return 'N/A';
            }
            
            const hours = Math.floor(decimalHours);
            const minutes = Math.round((decimalHours - hours) * 60);
            
            // Handle cases where rounding might give 60 minutes
            if (minutes === 60) {
                return `${hours + 1} Hour${hours + 1 !== 1 ? 's' : ''}`;
            }
            
            let result = '';
            
            if (hours > 0) {
                result += `${hours} Hour${hours !== 1 ? 's' : ''}`;
            }
            
            if (minutes > 0) {
                if (hours > 0) {
                    result += ' and ';
                }
                result += `${minutes} Minute${minutes !== 1 ? 's' : ''}`;
            }
            
            // Handle cases like 0.00 hours
            if (hours === 0 && minutes === 0) {
                return '0 Hours';
            }
            
            return result || 'N/A';  // Fallback to N/A if empty
        },

        clearFormErrMsg() {
            this.hasError = false;
            this.lastnameErrMsg = [];
            this.firstnameErrMsg = [];
            this.ojtErrMsg = [];
            this.studentErrMsg = [];
        },

        clearFormInput() {
            this.time_out = ''; 
            this.time_in = '';
            this.trainee_id = '';
            this.profile = '';
            this.student_number = '';

        },

        toggleAll() {
            if (this.selectAll) {
                // Use the already filtered list from applyFilters()
                let filteredTraineeIds = this.filteredList.map(time => time.time_id);
                
                // Merge selected time, avoiding duplicates
                this.selectedTrainees = [...new Set([...this.selectedTrainees, ...filteredTraineeIds])];
            } else {
                // Deselect only filtered trainee, keep selections from other pages
                let filteredTraineeIds = this.filteredList.map(time => time.time_id);
                
                this.selectedTrainees = this.selectedTrainees.filter(id => !filteredTraineeIds.includes(id));
            }
        },

        toggleSelection(time_id) {
            if (this.selectedTrainees.includes(time_id)) {
                this.selectedTrainees = this.selectedTrainees.filter(id => id !== time_id);
            } else {
                this.selectedTrainees.push(time_id);
            }
        },

        deleteSelectedTime() {
            if (this.selectedTrainees.length === 0) {
                console.warn('No Time selected for deletion.');
                return;
            }

            console.log('Sending time_id:', JSON.stringify(this.selectedTrainees));

            if (!confirm(`Are you sure you want to delete ${this.selectedTrainees.length} trainee(s)?`)) return;

            fetch('databases/delete-selected-time.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `time_id=${encodeURIComponent(JSON.stringify(this.selectedTrainees))}`
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
                    this.studentList = this.studentList.filter(t => !this.selectedTrainees.includes(t.time_id));
                    this.selectedTrainees = [];
                    this.applyFilters();
                    this.selectAll = false;
                    this.showAlert('trainee(s) deleted successfully.', 'success', 'text-xs');
                } else {
                    this.showAlert(data.message || 'Failed to delete selected trainee(s).', 'error');
                }
            })
            .catch(error => {
                console.error('Bulk Delete Error:', error);
                this.showAlert('An error occurred while deleting.', 'error');
            });
        },

        formatDateTimeNoSeconds(datetime) {
            if (!datetime) return '';
            const date = new Date(datetime);
            const pad = n => n.toString().padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        },
        confirmImport() {
    if (confirm('Are you sure you want to upload this file?')) {
        const formData = new FormData(document.getElementById('importForm'));
        fetch('databases/import-time.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                if (result.errors && result.errors.length > 0) {
                    result.errors.forEach((error, index) => {
                        setTimeout(() => {
                            alert(`Error ${index + 1} of ${result.errors.length}:\n${error}`);
                        }, index * 500);
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, result.errors.length * 500 + 500);
                } else {
                    window.location.reload();
                }
            } else {
                alert(result.message);
                if (result.errors && result.errors.length > 0) {
                    result.errors.forEach((error, index) => {
                        setTimeout(() => {
                            alert(`Error ${index + 1}:\n${error}`);
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





    }" x-init="getTrainee(); getTrainee_id();" x-cloak>

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
                        <div class="flex items-center ">
                            <div class="flex flex-col gap-2 ">
                                <span class="text-xl font-bold">TIME MONITORING</span>
                                <div class="flex items-center gap-4 shadow-pressDownDeep bg-gray-50 py-3 px-3 justify-center">

                                    <div>
                                        <label for="startDate" class="mr-2">From:</label>
                                        <input type="date" id="startDate" x-model="startDate" @change="applyFilters()"
                                            class="border p-1 rounded">
                                    </div>
                                    <div>
                                        <label for="endDate" class="mr-2">To:</label>
                                        <input type="date" id="endDate" x-model="endDate" @change="applyFilters()"
                                            class="border p-1 rounded">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="rounded-md p-4 pb-5 overflow-x-scroll md:overflow-x-hidden shadow-pressDownDeep bg-gray-50 mt-5">
                            <!-- Search -->
                            <div class="flex items-center justify-between gap-4 mb-4">
                                <div class="flex items-center gap-4">
                                     <div class="relative flex items-center bg-gray-50">
                                        <input type="text" class="border shadow-pressDownDeep p-1 rounded-full pl-10 border  outline-none" id="search" x-model="searchTerm" @input="applyFilters" placeholder="Search...">
                                        <i class="fa-solid fa-magnifying-glass absolute top-1/2 -translate-y-1/2 left-4 text-slate-400 text-sm"></i>
                                    </div>

                                    <div class="md:text-xs flex">
                                        <label for="perPage">Show:</label>
                                        <select x-model="perPage" @change="applyFilters()">
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="30">30</option>
                                        </select>
                                    </div>

                                    <div  class="shadow-pressDownDeep bg-gray-50 border py-2 px-4 focus:border-dashColor outline-none rounded-md">
                                        <label for="perPage">Filter:</label>
                                        <select x-model="filterPosition" @change="applyFilters()">
                                            <option value="all">All</option>
                                            <option value="student">Students</option>
                                            <option value="teacher">Teachers</option>
                                            <option value="visitor">Visitors</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <div class="relative">
                                        <button class="flex gap-2 items-center justify-center w-28 py-2 bg-Sdarkblue hover:bg-Sblue duration-500 text-white font-semibold text-sm py-2 px-4 shadow-lg rounded" onclick="exportToExcel()">
                                            <i class="fa-solid fa-cloud-arrow-down"></i>
                                            <span>Export</span>
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <!--  Table -->
                            <table class="w-full" id="traineeTable">
                                <thead class="text-black text-xs border-b border-gray-300">
                                    <tr>
                                        <th class="p-2">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                                :checked="paginatedList.length > 0 && paginatedList.every(t => selectedTrainees.includes(t.time_id))">
                                        </th>
                                        <th class="p-2">STUDENT ID</th>


                                        <th class="p-2 cursor-pointer relative group" @click="setSort('lastname')">
                                            <span>LAST NAME</span>
                                            <i class="fa-solid opacity-0 group-hover:opacity-100 ml-1 transition-opacity"
                                                :class="{
                                                'fa-arrow-down': sortBy !== 'lastname' || (sortBy === 'lastname' && sortAsc), 
                                                'fa-arrow-up': sortBy === 'lastname' && !sortAsc
                                            }">
                                            </i>
                                        </th>
                                        <th class="p-2 cursor-pointer relative group" @click="setSort('firstname')">
                                            <span>FIRST NAME</span>
                                            <i class="fa-solid opacity-0 group-hover:opacity-100 ml-1 transition-opacity"
                                                :class="{
                                                'fa-arrow-down': sortBy !== 'firstname' || (sortBy === 'firstname' && sortAsc), 
                                                'fa-arrow-up': sortBy === 'firstname' && !sortAsc
                                            }">
                                            </i>
                                        </th>
                                        <th x-show="filterPosition === 'student'"class="p-2">SECTION</th>
                                        <th x-show="filterPosition === 'student'"class="p-2">YEAR LEVEL</th>
                                        <th class="p-2">TIME IN</th>
                                        <th class="p-2">TIME OUT</th>
                                        <th class="p-2">ACTIONS</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <template x-if="paginatedList.length === 0">
                                        <tr class="border-b border-gray-300 text-sm text-center">
                                            <td colspan="8" class="p-2 text-gray-500">No data available</td>
                                        </tr>
                                    </template>

                                    <template x-for="time in paginatedList" :key="time.time_id">
                                        <tr class="border-b border-gray-300 md:text-xs text-sm text-center"
                                            :class="{ 'text-gray-500': time.is_hidden === '1' }">
                                            <td class="px-4 py-2">
                                                <input type="checkbox" @click="toggleSelection(time.time_id)"
                                                    :checked="selectedTrainees.includes(time.time_id)">
                                            </td>
                                            <td class="p-2" x-show="false" x-text="time.time_id"></td>
                                            <td class="p-2" x-text="time.student_id"></td>
                                            <td class="p-2" x-text="time.lastname"></td>
                                            <td class="p-2" x-text="time.firstname"></td>
                                            <td class="p-2" x-show="filterPosition === 'student'" x-text="time.section"></td>
                                            <td class="p-2" x-show="filterPosition === 'student'" x-text="time.year_level"></td>
                                            <td class="p-2" x-text="formatDateTime(time.time_in)"></td>
                                            <td class="p-2" x-text="formatDateTime(time.time_out)"></td>

                                            <td class="p-2">
                                                <!-- Edit Button -->
                                                <i class="fa-solid fa-pen-to-square cursor-pointer"
                                                    @click="modalEdit = true;
                                                            time_id = time.time_id;
                                                            trainee_id = time.trainee_id;
                                                            time_in = time.time_in;
                                                            time_out = time.time_out;">
                                                </i>

                                                <!-- Delete Button -->
                                                <i class="fa-solid fa-trash text-red-500 cursor-pointer"

                                                    x-on:click="deleteTime(time)">
                                                </i>
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
                                        x-show="selectedTrainees.length > 0 && paginatedList.length > 0"
                                        class="flex items-center gap-3">
                                        <span x-text="selectedTrainees.length + ' record(s) selected'"></span>
                                        <button class="bg-red-600 text-white px-2 py-1 rounded" x-on:click="deleteSelectedTime()">
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
                                                :class="{'bg-ueRed text-white font-bold': currentPage === page, 'bg-white text-gray-700': currentPage !== page}"
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

       
        <!-- edit Trainee Modal -->
        <div class="fixed py-10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pb-10 w-[45%] h-79 bg-white shadow-btnShadow z-50"
            x-show="modalEdit"
            x-on:click.outside="modalEdit = false; clearFormInput(); clearFormErrMsg()"
            x-cloak>

            <div class="flex px-14 items-center text-black p-2 ">
                <h1 class="font-bold text-2xl mb-2">EDIT TIME IN TIME OUT</h1>
            </div>

            <div class="relative px-14">
                <form @submit.prevent="submitEditForm">
                    <input type="hidden" x-model="time_id" name="time_id">
                    <div class="flex gap-5">
                        <!-- Time In -->
                        <div class="flex flex-col justify-between gap-2 mt-2">
                            <label for="time_in" class="font-semibold">Time In</label>
                            <input type="datetime-local"
                                class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                                name="time_in"
                                :value="formatDateTimeNoSeconds(time_in)"
                                @input="time_in = $event.target.value"
                                :min="minDateTime"
                                @change="validateForm()" required>
                        </div>

                        <!-- Time Out -->
                        <div class="flex flex-col justify-between gap-2 mt-2">
                            <label for="time_out" class="font-semibold">Time Out</label>
                            <input type="datetime-local"
                                class="pl-2 p-2 border-2 shadow-inputShadow outline-none"
                                name="time_out"
                                :value="formatDateTimeNoSeconds(time_out)"
                                @input="time_out = $event.target.value"
                                :min="minDateTime"
                                @change="validateForm()" required>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-5">
                        <button type="button" class="w-32 text-white bg-red-600 py-2 hover:bg-red-500"
                            x-on:click="modalEdit = false; clearFormInput(); clearFormErrMsg()">
                            Cancel
                        </button>

                        <button type="submit" class="w-32 py-2 bg-ccssGreen text-white hover:bg-green-500">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
    </div>

    <script>
        function createTempTable() {
            const originalTable = document.getElementById("traineeTable");
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

            // Prepare the data for export
            const dataForExport = filteredData.map(time => ({
                "Student ID": time.student_id || '',
                "Last Name": time.lastname || '',
                "First Name": time.firstname || '',
                "Time In": time.time_in ? formatExcelDateTime(time.time_in) : 'N/A',
                "Time Out": time.time_out ? formatExcelDateTime(time.time_out) : 'N/A'
            }));

            // Create worksheet
            const worksheet = XLSX.utils.json_to_sheet(dataForExport);

            // Create workbook
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Trainee Time Records");

            // Set column widths
            worksheet["!cols"] = [{
                    wch: 15
                }, // Student ID
                {
                    wch: 20
                }, // Last Name
                {
                    wch: 20
                }, // First Name
                {
                    wch: 25
                }, // Time In
                {
                    wch: 25
                }
            ];

            // Export the file
            XLSX.writeFile(workbook, "Trainee_Time_Records.xlsx");
        }

        // Helper function to format date for Excel
        function formatExcelDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
        }

        // Helper function to format hours for export
        function formatHoursForExport(hours) {
            if (!hours) return '0 Minutes';
            const h = Math.floor(hours);
            const m = Math.round((hours - h) * 60);
            return `${h > 0 ? h + ' Hour' + (h !== 1 ? 's' : '') + ' ' : ''}${m} Minute` + (m !== 1 ? 's' : '');
        }
    </script>
</body>

</html>