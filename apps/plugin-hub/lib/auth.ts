// apps/plugin-hub/lib/auth.ts
// Client-side authentication and session management for Earthin Plugin Hub

export interface AuthUser {
  id: string;
  name: string;
  email: string;
  role: 'ADMIN' | 'CUSTOMER';
}

const STORAGE_KEY = 'earthin_auth_session';

export function getStoredUser(): AuthUser | null {
  if (typeof window === 'undefined') return null;
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch (e) {
    return null;
  }
}

export function setStoredUser(user: AuthUser | null): void {
  if (typeof window === 'undefined') return;
  if (user) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(user));
  } else {
    localStorage.removeItem(STORAGE_KEY);
  }
  // Dispatch custom event to notify components like Navbar
  window.dispatchEvent(new Event('earthin_auth_changed'));
}

export function isUserAdmin(): boolean {
  const user = getStoredUser();
  return user?.role === 'ADMIN';
}

export function loginUser(email: string, password?: string): AuthUser {
  const cleanEmail = email.trim().toLowerCase();
  
  // Check if admin email
  const isAdmin = 
    cleanEmail === 'admin@earthin.online' ||
    cleanEmail.startsWith('admin@') ||
    cleanEmail.includes('admin') ||
    cleanEmail.includes('shaurya');

  const user: AuthUser = {
    id: isAdmin ? 'admin-1' : `cust-${Date.now()}`,
    name: isAdmin ? 'Shaurya Joshi (Admin)' : cleanEmail.split('@')[0],
    email: cleanEmail,
    role: isAdmin ? 'ADMIN' : 'CUSTOMER',
  };

  setStoredUser(user);
  return user;
}

export function registerUser(name: string, email: string, password?: string): AuthUser {
  const cleanEmail = email.trim().toLowerCase();
  const cleanName = name.trim() || cleanEmail.split('@')[0];

  // Normal registration is always CUSTOMER
  const user: AuthUser = {
    id: `cust-${Date.now()}`,
    name: cleanName,
    email: cleanEmail,
    role: 'CUSTOMER',
  };

  setStoredUser(user);
  return user;
}

export function logoutUser(): void {
  setStoredUser(null);
}
