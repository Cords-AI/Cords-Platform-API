require('dotenv-flow').config();
const fs = require('fs');

const responseFile = './api-tests/response.json';

module.exports = async function request(url, options = {}) {
  if (fs.existsSync(responseFile)) {
    fs.truncateSync(responseFile, 0);
  }

  const response = await fetch(`${process.env.BASE_URL}${url}`, options);
  const status = response.status;
  const headers = Object.fromEntries(response.headers)
  let body;
  try {
    body = await response.json();
  }
  catch(e) {
    body = await response.text();
  }

  fs.writeFileSync(responseFile, JSON.stringify({
    status,
    headers,
    body,
  }, null, 2));

  return {
    status,
    headers,
    body,
  }
};
