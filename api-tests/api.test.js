const fetch = require("./FetchDecorator");
require('dotenv-flow').config();

test('getUser', async () => {
  const response = await fetch(`${process.env.BASE_URL}/admin/users/${process.env.USER_ID}`, {
    "headers": {
      "x-csrf-token": process.env.X_CSRF_TOKEN,
      "cookie": process.env.COOKIE,
    },
  });

  expect(response.status).toBe(200);
});

test('getUsers', async () => {
  const response = await fetch(`${process.env.BASE_URL}/admin/users`, {
    "headers": {
      "x-csrf-token": process.env.X_CSRF_TOKEN,
      "cookie": process.env.COOKIE,
    },
  });

  expect(response.status).toBe(200);
});
