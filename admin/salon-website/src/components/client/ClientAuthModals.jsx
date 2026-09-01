import { useSalon } from '../../context/SalonContext'
import ClientLogin from './ClientLogin'
import ClientRegister from './ClientRegister'

export default function ClientAuthModals() {
  const { authModal } = useSalon()

  if (!authModal) return null

  if (authModal === 'register') {
    return <ClientRegister />
  }

  return <ClientLogin />
}
