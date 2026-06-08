<!-- SUMMER SALE BAR -->
<section class="max-w-md border border-[#d9c8b2] ">

    <!-- COUNTDOWN -->
    <div class="py-3 bg-[#f7f6f4]">
        <h3 class="text-center text-[14px] md:text-[16px] font-serif font-semibold uppercase text-[#4b4b4b] ">
            Sale Ends In
        </h3>

        <div class="mt-3 flex items-start justify-center gap-2">

            <!-- DAYS -->
            <div class="text-center">
                <div class="flex gap-1">
                    <span id="saleDay1"
                        class="w-6 h-6 bg-[#efe9e2] flex items-center justify-center text-[#b11313] text-xl font-bold font-serif">
                        0
                    </span>
                    <span id="saleDay2"
                        class="w-6 h-6 bg-[#efe9e2] flex items-center justify-center text-[#b11313] text-xl font-bold font-serif">
                        1
                    </span>
                </div>
                <p class="mt-1 text-[10px] font-semibold text-slate-700">
                    Days
                </p>
            </div>

            <div class="text-2xl md:text-4xl font-bold text-[#555] mt-1">:</div>

            <!-- HOURS -->
            <div class="text-center">
                <div class="flex gap-1">
                    <span id="saleHour1"
                        class="w-6 h-6 bg-[#efe9e2] flex items-center justify-center text-[#b11313] text-xl font-bold font-serif">
                        0
                    </span>
                    <span id="saleHour2"
                        class="w-6 h-6 bg-[#efe9e2] flex items-center justify-center text-[#b11313] text-xl font-bold font-serif">
                        0
                    </span>
                </div>
                <p class="mt-1 text-[10px] font-semibold text-slate-700">
                    Hours
                </p>
            </div>

            <div class="text-2xl md:text-4xl font-bold text-[#555] mt-1">:</div>

            <!-- MINUTES -->
            <div class="text-center">
                <div class="flex gap-1">
                    <span id="saleMinute1"
                        class="w-6 h-6 bg-[#efe9e2] flex items-center justify-center text-[#b11313] text-xl font-bold font-serif">
                        0
                    </span>
                    <span id="saleMinute2"
                        class="w-6 h-6 bg-[#efe9e2] flex items-center justify-center text-[#b11313] text-xl font-bold font-serif">
                        0
                    </span>
                </div>
                <p class="mt-1 text-[10px] font-semibold text-slate-700">
                    Minutes
                </p>
            </div>

            <div class="text-2xl md:text-4xl font-bold text-[#555] mt-1">:</div>

            <!-- SECONDS -->
            <div class="text-center">
                <div class="flex gap-1">
                    <span id="saleSecond1"
                        class="w-6 h-6 bg-[#efe9e2] flex items-center justify-center text-[#b11313] text-xl font-bold font-serif">
                        0
                    </span>
                    <span id="saleSecond2"
                        class="w-6 h-6 bg-[#efe9e2] flex items-center justify-center text-[#b11313] text-xl font-bold font-serif">
                        0
                    </span>
                </div>
                <p class="mt-1 text-[10px] font-semibold text-slate-700">
                    Seconds
                </p>
            </div>

        </div>
    </div>

    <!-- BANNER -->
    <div class="relative h-[140px] md:h-[160px] overflow-hidden">

        <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=1920"
            class="absolute inset-0 w-full h-full object-cover"
            alt="Sale Banner">

        <div class="absolute inset-0 bg-black/20"></div>

        <div class="relative z-10 flex h-full flex-col items-center justify-center text-center">

            <h2 class="font-serif text-[20px] text-[#4b2411] uppercase leading-none">
                Summer Sale
            </h2>

            <p class="mt-2 text-[12px] text-[#6a584a] uppercase">
                Get <span class="font-bold text-[#3d2b20]">30% Off</span> On Prepaid Order
            </p>

            <a href="#"
                class="mt-4 border border-[#8b705d] px-6 py-1.5 md:px-10 md:py-2 text-[10px] uppercase tracking-wide text-[#4b2411] hover:bg-[#4b2411] hover:text-white duration-300">
                Shop Now
            </a>

        </div>

    </div>

</section>

<script>
    function updateSaleCountdown() {

        const currentDate = new Date();

        const targetDate = new Date();
        targetDate.setDate(currentDate.getDate() + 1);
        targetDate.setHours(0, 0, 0, 0);

        const remainingTime = targetDate - currentDate;

        const totalDays = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
        const totalHours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const totalMinutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
        const totalSeconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

        const dayValue = String(totalDays).padStart(2, "0");
        const hourValue = String(totalHours).padStart(2, "0");
        const minuteValue = String(totalMinutes).padStart(2, "0");
        const secondValue = String(totalSeconds).padStart(2, "0");

        document.getElementById("saleDay1").textContent = dayValue[0];
        document.getElementById("saleDay2").textContent = dayValue[1];

        document.getElementById("saleHour1").textContent = hourValue[0];
        document.getElementById("saleHour2").textContent = hourValue[1];

        document.getElementById("saleMinute1").textContent = minuteValue[0];
        document.getElementById("saleMinute2").textContent = minuteValue[1];

        document.getElementById("saleSecond1").textContent = secondValue[0];
        document.getElementById("saleSecond2").textContent = secondValue[1];
    }

    updateSaleCountdown();
    setInterval(updateSaleCountdown, 1000);
</script>