    <script>
        //client id fcgT1tdETkuIL39hn3DWFg
        // Replace with your Zoom API key and secret
        //secret token DlXGtGveQ7C7luW9huw6-g
        //verification token: f21R7BH2SOKHCp0LREVRXg

        const apiKey = 'Z3yCj3McSWWx6IlCuLqk_A';
        const apiSecret = 'aNVO2fePnBpcCtel9QtM72WpiT20VMes';

        // Function to generate a Zoom meeting link
        async function createZoomMeeting() {
            const apiUrl = 'https://api.zoom.us/v2/users/me/meetings';

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${apiKey}.${apiSecret}`,
                },
                body: JSON.stringify({
                    topic: 'Your Meeting Topic',
                    type: 2, // Scheduled meeting
                    // Add other parameters as needed
                }),
            });

            const responseData = await response.json();

            if (response.ok) {
                const meetingLink = responseData.join_url;
                console.log('Meeting link:', meetingLink);
            } else {
                console.error('Error creating Zoom meeting:', responseData);
            }
        }

        // Call the function to create a Zoom meeting when the page loads
        window.onload = createZoomMeeting;
    </script>