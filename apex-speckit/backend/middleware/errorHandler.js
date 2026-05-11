function errorHandler(err, req, res, next) {
  console.error(err);

  if (res.headersSent) {
    return next(err);
  }

  const statusCode = err.status || (err.code === '23505' ? 409 : 500);
  const errorMessage = err.code === '23505' ? 'Already exists' : 'Internal server error';

  const response = {
    success: false,
    error: statusCode === 409 ? errorMessage : 'Internal server error',
  };

  if (process.env.NODE_ENV !== 'production') {
    response.details = err.message ? [err.message] : [];
    response.stack = err.stack;
  }

  if (statusCode === 409) {
    response.error = errorMessage;
  }

  res.status(statusCode).json(response);
}

module.exports = errorHandler;
