'use client';

import { useState } from 'react';
import SignInForm from '@/components/SignInForm';
import RegisterForm from '@/components/RegisterForm';
import Navbar from '@/components/Navbar';

export default function LoginPage() {
  const [activeTab, setActiveTab] = useState('signin');

  return (
    <>
      <Navbar />
      <main className="auth-page">
        <div className="auth-container">
          <div className="auth-tabs">
            <button
              className={`tab ${activeTab === 'signin' ? 'active' : ''}`}
              onClick={() => setActiveTab('signin')}
            >
              Sign In
            </button>
            <button
              className={`tab ${activeTab === 'register' ? 'active' : ''}`}
              onClick={() => setActiveTab('register')}
            >
              Register
            </button>
          </div>

          <div className="auth-content">
            {activeTab === 'signin' && <SignInForm />}
            {activeTab === 'register' && <RegisterForm />}
          </div>
        </div>
      </main>
    </>
  );
}
