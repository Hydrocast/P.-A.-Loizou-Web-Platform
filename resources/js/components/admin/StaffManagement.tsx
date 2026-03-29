import { useForm } from '@inertiajs/react';
import { Plus, Edit, UserX, UserCheck, Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import Modal, { ConfirmDialog } from '@/components/public/Modal';

type StaffAccount = {
  staff_id: number;
  username: string;
  role: 'Employee' | 'Administrator';
  full_name: string | null;
  account_status: 'Active' | 'Inactive';
};

type StaffManagementProps = {
  accounts: StaffAccount[];
  flash?: {
    success?: string;
    error?: string;
  };
  currentStaffUsername?: string | null;
};

export default function StaffManagement({
  accounts,
  flash = {},
  currentStaffUsername = null,
}: StaffManagementProps) {
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [selectedStaff, setSelectedStaff] = useState<StaffAccount | null>(null);
  const [isStatusConfirmOpen, setIsStatusConfirmOpen] = useState(false);
  const [staffToToggle, setStaffToToggle] = useState<StaffAccount | null>(null);
  const [showCreatePassword, setShowCreatePassword] = useState(false);
  const [showEditPassword, setShowEditPassword] = useState(false);

  const createForm = useForm({
    username: '',
    password: '',
    role: 'Employee' as 'Employee' | 'Administrator',
    full_name: '',
  });

  const editForm = useForm({
    full_name: '',
    password: '',
  });

  const statusForm = useForm({
    account_status: 'Inactive' as 'Active' | 'Inactive',
  });

  const closeAddModal = () => {
    setIsAddModalOpen(false);
    setShowCreatePassword(false);
  };

  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setSelectedStaff(null);
    setShowEditPassword(false);
  };

  const openAddModal = () => {
    createForm.reset();
    createForm.clearErrors();
    setShowCreatePassword(false);
    setIsAddModalOpen(true);
  };

  const openEditModal = (staffMember: StaffAccount) => {
    setSelectedStaff(staffMember);
    editForm.setData({
      full_name: staffMember.full_name ?? '',
      password: '',
    });
    editForm.clearErrors();
    setShowEditPassword(false);
    setIsEditModalOpen(true);
  };

  const openToggleConfirm = (staffMember: StaffAccount) => {
    setStaffToToggle(staffMember);
    statusForm.setData(
      'account_status',
      staffMember.account_status === 'Active' ? 'Inactive' : 'Active'
    );
    statusForm.clearErrors();
    setIsStatusConfirmOpen(true);
  };

  const handleAddStaff = () => {
    createForm.post('/staff/accounts', {
      preserveScroll: true,
      onSuccess: () => {
        closeAddModal();
        createForm.reset();
      },
    });
  };

  const handleEditStaff = () => {
    if (!selectedStaff) return;

    editForm.put(`/staff/accounts/${selectedStaff.staff_id}`, {
      preserveScroll: true,
      onSuccess: () => {
        closeEditModal();
        editForm.reset('password');
      },
    });
  };

  const handleToggleStatus = () => {
    if (!staffToToggle) return;

    statusForm.patch(`/staff/accounts/${staffToToggle.staff_id}/status`, {
      preserveScroll: true,
      onSuccess: () => {
        setIsStatusConfirmOpen(false);
        setStaffToToggle(null);
      },
    });
  };

  return (
    <div className="overflow-hidden rounded-lg bg-white p-4 shadow-md sm:p-5 md:p-6">
      <div className="mb-5 flex flex-col gap-4 sm:mb-6 sm:flex-row sm:items-center sm:justify-between">
        <h2 className="text-xl font-semibold text-purple-900 sm:text-2xl">Staff Management</h2>

        <button
          onClick={openAddModal}
          className="flex w-full items-center justify-center rounded-lg bg-purple-600 px-4 py-2 text-white transition-colors cursor-pointer hover:bg-purple-700 sm:w-auto"
        >
          <Plus className="w-5 h-5 mr-2" />
          Create Staff Account
        </button>
      </div>

      {flash.success && (
        <div className="mb-4 rounded-md border border-green-200 bg-green-100 px-4 py-3 text-sm text-green-800 sm:text-base">
          {flash.success}
        </div>
      )}

      {flash.error && (
        <div className="mb-4 rounded-md border border-red-200 bg-red-100 px-4 py-3 text-sm text-red-800 sm:text-base">
          {flash.error}
        </div>
      )}

      {/* Staff Table */}
      <div className="md:hidden">
        {accounts.length === 0 ? (
          <div className="rounded-md border border-gray-200 px-4 py-8 text-center text-sm text-gray-500">
            No staff accounts found.
          </div>
        ) : (
          <div className="space-y-4">
            {accounts.map((staffMember) => (
              <div
                key={staffMember.staff_id}
                className="rounded-md border border-gray-200 p-4"
              >
                <div className="space-y-3">
                  <div>
                    <p className="text-xs font-medium uppercase tracking-wide text-gray-500">Username</p>
                    <p className="mt-1 break-all font-medium text-gray-900">{staffMember.username}</p>
                  </div>

                  <div>
                    <p className="text-xs font-medium uppercase tracking-wide text-gray-500">Full Name</p>
                    <p className="mt-1 wrap-break-word text-gray-700">{staffMember.full_name ?? '—'}</p>
                  </div>

                  <div className="flex flex-wrap gap-2">
                    <span
                      className={`inline-flex rounded px-2 py-1 text-xs font-semibold ${
                        staffMember.role === 'Administrator'
                          ? 'bg-purple-100 text-purple-800'
                          : 'bg-blue-100 text-blue-800'
                      }`}
                    >
                      {staffMember.role}
                    </span>

                    <span
                      className={`inline-flex rounded px-2 py-1 text-xs font-semibold ${
                        staffMember.account_status === 'Active'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      }`}
                    >
                      {staffMember.account_status}
                    </span>
                  </div>

                  <div className="flex flex-wrap gap-2 pt-1">
                    <button
                      onClick={() => openEditModal(staffMember)}
                      className="inline-flex items-center gap-2 rounded-md border border-purple-200 px-3 py-2 text-sm text-purple-700 transition-colors hover:bg-purple-50 cursor-pointer"
                      title="Edit Staff"
                    >
                      <Edit className="h-4 w-4" />
                      Edit
                    </button>

                    <button
                      onClick={() => openToggleConfirm(staffMember)}
                      className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors cursor-pointer ${
                        staffMember.account_status === 'Active'
                          ? 'border border-red-200 text-red-700 hover:bg-red-50'
                          : 'border border-green-200 text-green-700 hover:bg-green-50'
                      }`}
                      title={staffMember.account_status === 'Active' ? 'Deactivate' : 'Activate'}
                    >
                      {staffMember.account_status === 'Active' ? (
                        <UserX className="h-4 w-4" />
                      ) : (
                        <UserCheck className="h-4 w-4" />
                      )}
                      {staffMember.account_status === 'Active' ? 'Deactivate' : 'Activate'}
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="hidden w-full overflow-hidden md:block">
        <table className="w-full table-fixed">
          <thead className="bg-gray-50">
            <tr>
              <th className="w-[30%] px-4 py-3 text-left text-sm font-medium text-gray-700">Username</th>
              <th className="w-[30%] px-4 py-3 text-left text-sm font-medium text-gray-700">Full Name</th>
              <th className="w-[16%] px-4 py-3 text-left text-sm font-medium text-gray-700">Role</th>
              <th className="w-[14%] px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
              <th className="w-[10%] px-4 py-3 text-right text-sm font-medium text-gray-700">Actions</th>
            </tr>
          </thead>

          <tbody className="divide-y divide-gray-200">
            {accounts.length === 0 ? (
              <tr>
                <td colSpan={5} className="px-4 py-8 text-center text-gray-500">
                  No staff accounts found.
                </td>
              </tr>
            ) : (
              accounts.map((staffMember) => (
                <tr key={staffMember.staff_id} className="align-top transition-colors hover:bg-gray-50">
                  <td className="px-4 py-3 font-medium text-gray-900">
                    <div className="min-w-0 whitespace-normal break-all">
                      {staffMember.username}
                    </div>
                  </td>

                  <td className="px-4 py-3 text-gray-700">
                    <div className="min-w-0 whitespace-normal wrap-break-word">
                      {staffMember.full_name ?? '—'}
                    </div>
                  </td>

                  <td className="px-4 py-3">
                    <span
                      className={`inline-flex max-w-full whitespace-normal wrap-break-word rounded px-2 py-1 text-xs font-semibold ${
                        staffMember.role === 'Administrator'
                          ? 'bg-purple-100 text-purple-800'
                          : 'bg-blue-100 text-blue-800'
                      }`}
                    >
                      {staffMember.role}
                    </span>
                  </td>

                  <td className="px-4 py-3">
                    <span
                      className={`inline-flex max-w-full whitespace-normal wrap-break-word rounded px-2 py-1 text-xs font-semibold ${
                        staffMember.account_status === 'Active'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      }`}
                    >
                      {staffMember.account_status}
                    </span>
                  </td>

                  <td className="px-4 py-3">
                    <div className="flex items-start justify-end gap-2">
                      <button
                        onClick={() => openEditModal(staffMember)}
                        className="shrink-0 rounded p-2 text-purple-600 transition-colors cursor-pointer hover:bg-purple-50"
                        title="Edit Staff"
                      >
                        <Edit className="h-4 w-4" />
                      </button>

                      <button
                        onClick={() => openToggleConfirm(staffMember)}
                        className={`shrink-0 rounded p-2 transition-colors cursor-pointer ${
                          staffMember.account_status === 'Active'
                            ? 'text-red-600 hover:bg-red-50'
                            : 'text-green-600 hover:bg-green-50'
                        }`}
                        title={staffMember.account_status === 'Active' ? 'Deactivate' : 'Activate'}
                      >
                        {staffMember.account_status === 'Active' ? (
                          <UserX className="h-4 w-4" />
                        ) : (
                          <UserCheck className="h-4 w-4" />
                        )}
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Create Staff Modal */}
      <Modal isOpen={isAddModalOpen} onClose={closeAddModal} title="Create Staff Account">
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Username *</label>
            <input
              type="text"
              value={createForm.data.username}
              onChange={(e) => createForm.setData('username', e.target.value)}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                createForm.errors.username ? 'border-red-500' : 'border-gray-300'
              }`}
            />
            {createForm.errors.username && (
              <p className="mt-1 text-sm text-red-600">{createForm.errors.username}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Password *</label>

            <div className="relative">
              <input
                type={showCreatePassword ? 'text' : 'password'}
                value={createForm.data.password}
                onChange={(e) => createForm.setData('password', e.target.value)}
                className={`w-full px-4 py-2 pr-12 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                  createForm.errors.password ? 'border-red-500' : 'border-gray-300'
                }`}
                minLength={8}
                maxLength={64}
              />

              <button
                type="button"
                onClick={() => setShowCreatePassword((prev) => !prev)}
                className="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 cursor-pointer"
                aria-label={showCreatePassword ? 'Hide password' : 'Show password'}
                aria-pressed={showCreatePassword}
              >
                {showCreatePassword ? (
                  <EyeOff className="w-5 h-5" />
                ) : (
                  <Eye className="w-5 h-5" />
                )}
              </button>
            </div>

            {createForm.errors.password && (
              <p className="mt-1 text-sm text-red-600">{createForm.errors.password}</p>
            )}
            <p className="mt-1 text-xs text-gray-500">Must be between 8 and 64 characters.</p>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Role *</label>
            <select
              value={createForm.data.role}
              onChange={(e) => createForm.setData('role', e.target.value as 'Employee' | 'Administrator')}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 cursor-pointer ${
                createForm.errors.role ? 'border-red-500' : 'border-gray-300'
              }`}
            >
              <option value="Employee">Employee</option>
              <option value="Administrator">Administrator</option>
            </select>
            {createForm.errors.role && (
              <p className="mt-1 text-sm text-red-600">{createForm.errors.role}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input
              type="text"
              value={createForm.data.full_name}
              onChange={(e) => createForm.setData('full_name', e.target.value)}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                createForm.errors.full_name ? 'border-red-500' : 'border-gray-300'
              }`}
            />
            {createForm.errors.full_name && (
              <p className="mt-1 text-sm text-red-600">{createForm.errors.full_name}</p>
            )}
          </div>

          <div className="flex flex-col-reverse gap-3 pt-4 sm:flex-row sm:justify-end">
            <button
              onClick={closeAddModal}
              className="w-full rounded-lg border border-gray-300 px-6 py-2 font-medium transition-colors cursor-pointer hover:bg-gray-50 sm:w-auto"
            >
              Cancel
            </button>
            <button
              onClick={handleAddStaff}
              disabled={createForm.processing}
              className="w-full rounded-lg bg-purple-600 px-6 py-2 font-medium text-white transition-colors cursor-pointer hover:bg-purple-700 disabled:opacity-60 sm:w-auto"
            >
              Create Staff
            </button>
          </div>
        </div>
      </Modal>

      {/* Edit Staff Modal */}
      <Modal isOpen={isEditModalOpen} onClose={closeEditModal} title="Edit Staff Account">
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input
              type="text"
              value={selectedStaff?.username ?? ''}
              disabled
              className="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
            />
            <p className="mt-1 text-xs text-gray-500">Username cannot be changed.</p>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input
              type="text"
              value={editForm.data.full_name}
              onChange={(e) => editForm.setData('full_name', e.target.value)}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                editForm.errors.full_name ? 'border-red-500' : 'border-gray-300'
              }`}
            />
            {editForm.errors.full_name && (
              <p className="mt-1 text-sm text-red-600">{editForm.errors.full_name}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">New Password</label>

            <div className="relative">
              <input
                type={showEditPassword ? 'text' : 'password'}
                value={editForm.data.password}
                onChange={(e) => editForm.setData('password', e.target.value)}
                className={`w-full px-4 py-2 pr-12 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                  editForm.errors.password ? 'border-red-500' : 'border-gray-300'
                }`}
                minLength={8}
                maxLength={64}
                placeholder="Leave blank to keep current password"
              />

              <button
                type="button"
                onClick={() => setShowEditPassword((prev) => !prev)}
                className="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 cursor-pointer"
                aria-label={showEditPassword ? 'Hide new password' : 'Show new password'}
                aria-pressed={showEditPassword}
              >
                {showEditPassword ? (
                  <EyeOff className="w-5 h-5" />
                ) : (
                  <Eye className="w-5 h-5" />
                )}
              </button>
            </div>

            {editForm.errors.password && (
              <p className="mt-1 text-sm text-red-600">{editForm.errors.password}</p>
            )}
            <p className="mt-1 text-xs text-gray-500">
              Leave blank to keep current password. Must be 8-64 characters if changed.
            </p>
          </div>

          <div className="flex flex-col-reverse gap-3 pt-4 sm:flex-row sm:justify-end">
            <button
              onClick={closeEditModal}
              className="w-full rounded-lg border border-gray-300 px-6 py-2 font-medium transition-colors cursor-pointer hover:bg-gray-50 sm:w-auto"
            >
              Cancel
            </button>
            <button
              onClick={handleEditStaff}
              disabled={editForm.processing}
              className="w-full rounded-lg bg-purple-600 px-6 py-2 font-medium text-white transition-colors cursor-pointer hover:bg-purple-700 disabled:opacity-60 sm:w-auto"
            >
              Save Changes
            </button>
          </div>
        </div>
      </Modal>

      {/* Status Toggle Confirmation */}
      <ConfirmDialog
        isOpen={isStatusConfirmOpen}
        onClose={() => setIsStatusConfirmOpen(false)}
        onConfirm={handleToggleStatus}
        title={staffToToggle?.account_status === 'Active' ? 'Deactivate Staff Account' : 'Activate Staff Account'}
        message={`Are you sure you want to ${
          staffToToggle?.account_status === 'Active' ? 'deactivate' : 'activate'
        } the account for "${staffToToggle?.full_name ?? staffToToggle?.username}"?${
          currentStaffUsername !== null && staffToToggle?.username === currentStaffUsername
            ? ' You cannot deactivate your own account.'
            : ''
        }`}
        confirmText={staffToToggle?.account_status === 'Active' ? 'Deactivate' : 'Activate'}
        cancelText="Cancel"
        type={staffToToggle?.account_status === 'Active' ? 'danger' : 'success'}
      />
    </div>
  );
}