import { Head } from '@inertiajs/react';
import OrderHistory from '../../../components/customer/OrderHistory';
import CustomerAccountLayout from '../../../layouts/CustomerAccountLayout';

export default function OrderHistoryPage() {
  return (
    <>
      <Head title="Order History" />

      <CustomerAccountLayout active="orders">
        <OrderHistory />
      </CustomerAccountLayout>
    </>
  );
}