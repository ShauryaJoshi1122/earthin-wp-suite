# Earthin Plugin Hub

The official web platform and marketplace for discovering, downloading, managing, and licensing Earthin WordPress plugins.

**Live Target Domain:** [https://plugins.earthin.online](https://plugins.earthin.online)

---

## Architecture & Technology Stack

- **Framework:** Next.js 14 (App Router)
- **Language:** TypeScript 5 (Strict Mode enabled)
- **Styling:** Tailwind CSS with Earthin design tokens
- **Icons:** Lucide React
- **Validation:** Type-safe schema validation
- **Testing:** Vitest automated test suite

---

## Directory Structure

```text
apps/plugin-hub/
├── app/                  # Next.js App Router routes & pages
│   ├── layout.tsx        # Global root layout with Navbar & Footer
│   ├── page.tsx          # Marketing homepage
│   ├── plugins/          # Plugin directory & product pages
│   ├── pricing/          # Freemium & licensing pricing guide
│   ├── docs/             # Knowledge base & documentation
│   ├── changelog/        # Product updates & security advisories
│   ├── support/          # Customer help desk & contact form
│   ├── not-found.tsx     # Custom 404 page
│   └── globals.css       # Tailwind base, utilities & tokens
├── components/           # Reusable UI & layout components
│   ├── layout/           # Navbar, Footer, Mobile navigation
│   ├── ui/               # Button, Badge, Card primitives
│   └── plugins/          # Plugin cards, badges, comparison tables
├── lib/                  # Shared utilities, types, catalog data
├── tests/                # Automated test specifications
├── .env.example          # Environment variable template
├── package.json          # Application package manifest
└── tsconfig.json         # Strict TypeScript compiler configuration
```

---

## Local Development

### 1. Prerequisites
- Node.js >= 18.18 (Node 20+ recommended)
- pnpm >= 8 (pnpm 9+ recommended)

### 2. Install Dependencies
From the repository root or inside `apps/plugin-hub`:
```bash
pnpm install
```

### 3. Environment Setup
Copy the example environment file:
```bash
cp .env.example .env.local
```

### 4. Run Development Server
```bash
pnpm --filter plugin-hub dev
# or from inside apps/plugin-hub:
pnpm dev
```
Open [http://localhost:3000](http://localhost:3000) in your browser.

### 5. Type Checking & Tests
```bash
pnpm --filter plugin-hub type-check
pnpm --filter plugin-hub test
pnpm --filter plugin-hub build
```

---

## Relationship with WordPress Plugin (`earthin-wp-suite`)

This web application lives alongside the `earthin-wp-suite` WordPress plugin in the same monorepo.
- WordPress plugin files remain at the repository root (`earthin-wp-suite.php`, `includes/`, `modules/`, `tests/`).
- PHP testing and CI workflows operate independently from the Next.js application.
- The web platform serves as the distribution, documentation, and update server for Earthin plugins.
