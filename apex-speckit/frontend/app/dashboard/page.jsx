'use client';

import { useAuth } from '@/lib/AuthContext';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import Navbar from '@/components/Navbar';

export default function DashboardPage() {
  const { user, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/login');
    }
  }, [user, isLoading, router]);

  if (isLoading) {
    return <div>Loading...</div>;
  }

  if (!user) {
    return null;
  }

  return (
    <>
      <Navbar />
      <main className="dashboard">
        <h1>Welcome, {user.first_name}!</h1>
        <div className="user-info">
          <p><strong>Email:</strong> {user.email}</p>
          <p><strong>Organization:</strong> {user.organization || 'N/A'}</p>
          <p><strong>Country:</strong> {user.country}</p>
          <p><strong>City:</strong> {user.city || 'N/A'}</p>
        </div>
      </main>
    </>
  );
}
