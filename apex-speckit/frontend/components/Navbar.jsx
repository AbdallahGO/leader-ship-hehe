'use client';

import { useAuth } from '@/lib/AuthContext';
import Link from 'next/link';

export default function Navbar() {
  const { user, logout, isLoading } = useAuth();

  const handleLogout = async () => {
    await logout();
  };

  if (isLoading) {
    return <nav className="navbar">Loading...</nav>;
  }

  return (
    <nav className="navbar">
      <div className="navbar-container">
        <Link href="/" className="navbar-logo">
          Apex Summit
        </Link>
        <div className="navbar-menu">
          {user ? (
            <div className="user-menu">
              <span className="user-greeting">
                Hi, {user.first_name}
              </span>
              <div className="user-avatar">
                {user.first_name[0]}{user.last_name[0]}
              </div>
              <Link href="/dashboard" className="nav-link">
                Dashboard
              </Link>
              <button onClick={handleLogout} className="logout-btn">
                Logout
              </button>
            </div>
          ) : (
            <Link href="/login" className="sign-in-btn">
              Sign In
            </Link>
          )}
        </div>
      </div>
    </nav>
  );
}
