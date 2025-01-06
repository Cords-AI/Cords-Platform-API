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
