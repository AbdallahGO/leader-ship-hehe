require('dotenv').config();
const client = require('@sendgrid/client');
client.setApiKey(process.env.SENDGRID_API_KEY);

(async () => {
  try {
    const [response, body] = await client.request({ method: 'GET', url: '/v3/user/profile' });
    console.log('SendGrid auth check succeeded');
    console.log('statusCode=', response.statusCode);
    console.log('profile=', body);
  } catch (error) {
    console.error('SendGrid auth check failed');
    if (error.response && error.response.body) {
      console.error('response body=', JSON.stringify(error.response.body, null, 2));
    } else {
      console.error(error);
    }
    process.exit(1);
  }
})();
