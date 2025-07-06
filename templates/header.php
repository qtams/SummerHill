<div class="flex items-center h-20 w-full shadow-md bg-gray-100 px-4 justify-between">
    <div>
        <span class="font-bold text-2xl text-SdarkBlue">Summer Hill School Foundation Inc·</span>
    </div>
    
    <!-- Display date and time -->
    <div 
        class="font-bold text-xl text-SdarkBlue"
        x-data="{ time: '' }" 
        x-init="
            const options = { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit', year: 'numeric', month: 'long', day: 'numeric' };
            time = new Date().toLocaleString('en-US', options);
            setInterval(() => {
                time = new Date().toLocaleString('en-US', options);
            }, 1000);
        ">
        <span x-text="time"></span>
    </div>
</div>
