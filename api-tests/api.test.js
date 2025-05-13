const fetch = require("./FetchDecorator");
require('dotenv-flow').config();

test('getUser', async () => {
  const response = await fetch(`/admin/users/${process.env.USER_ID}`, {
    "headers": getHeaders(),
  });

  expect(response.status).toBe(200);
});

test('getUsers', async () => {
  const response = await fetch(`/admin/users`, {
    "headers": getHeaders(),
  });

  expect(response.status).toBe(200);
});

test('updateProfile', async() => {
  const response = await fetch("/authenticated/profile", {
    "headers": getHeaders(),
    "body": JSON.stringify({
      "organization": "abc",
      "purpose": "abc"
    }),
    "method": "POST"
  });

  expect(response.status).toBe(200);
})

function getHeaders() {
  return {
    "x-csrf-token": process.env.X_CSRF_TOKEN,
    "cookie": process.env.COOKIE
  }
}

test('getTerm', async() => {
  const response = await fetch("/term/terms-of-use");

  expect(response.status).toBe(200);
});

test('acceptTerm', async() => {
  const name = "terms-of-use"
  const response = await fetch(`/authenticated/agreements/accept/${name}`, {
    headers: getHeaders(),
    method: 'POST',
  });

  expect(response.status).toBe(200);
});

test('postLog', async () => {
  const body = {
    apiKey: process.env.TEST_API_KEY,
    ip: '127.0.0.1',
    searchString: 'test search',
    latitude: '12.34',
    longitude: '56.78',
    province: 'Ontario',
    country: 'Canada',
    postalCode: 'A1B2C3',
    type: 'search',
    sid: 'test-session-id',
    filters: ['filter1', 'filter2'],
  };
  const response = await fetch('/system/log', {
    method: 'POST',
    headers: {
      'x-api-key': process.env.SYSTEM_TOKEN,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(body),
  });
  expect(response.status).toBe(200);
  expect(response.body.data).toBe('success');
});
