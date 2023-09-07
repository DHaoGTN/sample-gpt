import http from 'k6/http';
import { check } from 'k6';

export let options = {
  stages: [
    { duration: '1s', target: 100 }, // Ramp up to 1000 VUs instantly and maintain for 30 seconds
  ],
};

export default function () {
  // Set the bearer token
  let token = '3|Qv5DSKybEUluMJHplM260aSDLghwhSbbfVH2WpR7';

  // Define the headers with the bearer token
  let headers = {
    Authorization: `Bearer ${token}`,
  };

  // Send GET request to the API endpoint with authentication header
  let response = http.get('http://localhost:8080/api/Bill?year=2022', { headers });

  // Optional: Check response status or perform other assertions
  check(response, {
    'is status 200': (r) => r.status === 200,
  });
}
