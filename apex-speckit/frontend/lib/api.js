const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:5000';

export const loginUser = async (email, password) => {
  const response = await fetch(`${API_URL}/api/auth/login`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ email, password })
  });

  if (!response.ok) {
    throw new Error('Login failed');
  }

  return response.json();
};

export const registerUser = async (data) => {
  const response = await fetch(`${API_URL}/api/auth/register`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });

  if (!response.ok) {
    throw new Error('Registration failed');
  }

  return response.json();
};

export const logoutUser = async () => {
  const response = await fetch(`${API_URL}/api/auth/logout`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json'
    }
  });

  if (!response.ok) {
    throw new Error('Logout failed');
  }

  return response.json();
};

export const getCurrentUser = async () => {
  const response = await fetch(`${API_URL}/api/auth/me`, {
    method: 'GET',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json'
    }
  });

  if (!response.ok) {
    if (response.status === 401) {
      return null;
    }
    throw new Error('Failed to fetch user');
  }

  const data = await response.json();
  return data.data?.user || null;
};

export const requestPasswordReset = async (email) => {
  const response = await fetch(`${API_URL}/api/auth/forgot-password`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ email })
  });

  return response.json();
};

export const resetPassword = async (token, password) => {
  const response = await fetch(`${API_URL}/api/auth/reset-password`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ token, password })
  });

  if (!response.ok) {
    throw new Error('Password reset failed');
  }

  return response.json();
};
