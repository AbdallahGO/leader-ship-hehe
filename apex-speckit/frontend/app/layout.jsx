import './globals.css';
import { AuthProvider } from '@/lib/AuthContext';

export const metadata = {
  title: 'Apex Leadership Summit 2026',
  description: 'Join us for the Apex Leadership Summit 2026'
};

export default function RootLayout({ children }) {
  return (
    <html lang="en">
      <body>
        <AuthProvider>
          {children}
        </AuthProvider>
      </body>
    </html>
  );
}
