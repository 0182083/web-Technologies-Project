document.addEventListener("DOMContentLoaded", function () {
    const facilitySelect = document.getElementById("facility_id");
    const dateInput = document.getElementById("slot_date");
    const checkBtn = document.getElementById("check_availability");
    const slotsContainer = document.getElementById("slots_container");

    checkBtn.addEventListener("click", function () {
        const facilityId = facilitySelect.value;
        const date = dateInput.value;

        if (!facilityId || !date) {
            alert("Please select a facility and date.");
            return;
        }

        slotsContainer.innerHTML = "<p>Checking availability...</p>";

        fetch(`/sports_booking_system/customer/fetch_slots.php?facility_id=${facilityId}&date=${date}`)
            .then(response => {
                if (!response.ok) throw new Error("Network response was not ok");
                return response.json();
            })
            .then(slots => {
                if (!Array.isArray(slots) || slots.length === 0) {
                    slotsContainer.innerHTML = "<p class='text-danger'>No slots available for this date.</p>";
                    return;
                }

                let html = "<form method='POST'>";
                html += "<table class='table table-bordered table-striped'><thead class='table-secondary'><tr>";
                html += "<th>Select</th><th>Date</th><th>Start Time</th><th>End Time</th></tr></thead><tbody>";

                slots.forEach(slot => {
                    html += `<tr>
                        <td><input type="radio" name="slot_id" value="${slot.slot_id}" required></td>
                        <td>${slot.slot_date}</td>
                        <td>${slot.start_time}</td>
                        <td>${slot.end_time}</td>
                    </tr>`;
                });

                html += "</tbody></table>";
                html += "<button type='submit' name='book' class='btn btn-success'>Confirm Booking & Pay</button>";
                html += "</form>";

                slotsContainer.innerHTML = html;
            })
            .catch(err => {
                slotsContainer.innerHTML = "<p class='text-danger'>Error fetching slots. Try again.</p>";
                console.error(err);
            });
    });
});
