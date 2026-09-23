import { execSync } from 'node:child_process'

export default async function globalTeardown() {
  execSync('php artisan e2e:reset-settings', { stdio: 'inherit' })
}
