import { useEffect, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { Plus, Edit, UserX, UserCheck, Eye, EyeOff } from 'lucide-react';
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
  const [successMessage, setSuccessMessage] = useState(flash.success ?? '');
  const [errorMessage, setErrorMessage] = useState(flash.error ?? '');
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

  useEffect(() => {
    setSuccessMessage(flash.success ?? '');
    setErrorMessage(flash.error ?? '');
  }, [flash.success, flash.error]);

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
    <div className="bg-white rounded-lg shadow-md p-6 overflow-hidden">
      <div className="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
        <h2 className="text-2xl font-semibold text-purple-900">Staff Management</h2>

        <button
          onClick={openAddModal}
          className="flex items-center justify-center bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors cursor-pointer sm:w-auto"
        >
          <Plus className="w-5 h-5 mr-2" />
          Create Staff Account
        </button>
      </div>

      {successMessage && (
        <div className="mb-4 p-4 bg-green-100 text-green-800 rounded-md border border-green-200">
          {successMessage}
        </div>
      )}

      {errorMessage && (
        <div className="mb-4 p-4 bg-red-100 text-red-800 rounded-md border border-red-200">
          {errorMessage}
        </div>
      )}

      {/* Staff Table */}
      <div className="w-full overflow-hidden">
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
                <tr key={staffMember.staff_id} className="hover:bg-gray-50 transition-colors align-top">
                  <td className="px-4 py-3 font-medium text-gray-900">
                    <div className="min-w-0 whitespace-normal break-all">
                      {staffMember.username}
                    </div>
                  </td>

                  <td className="px-4 py-3 text-gray-700">
                    <div className="min-w-0 whitespace-normal break-words">
                      {staffMember.full_name ?? '—'}
                    </div>
                  </td>

                  <td className="px-4 py-3">
                    <span
                      className={`inline-flex max-w-full whitespace-normal break-words px-2 py-1 rounded text-xs font-semibold ${
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
                      className={`inline-flex max-w-full whitespace-normal break-words px-2 py-1 rounded text-xs font-semibold ${
                        staffMember.account_status === 'Active'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      }`}
                    >
                      {staffMember.account_status}
                    </span>
                  </td>

                  <td className="px-4 py-3">
                    <div className="flex justify-end items-start gap-2">
                      <button
                        onClick={() => openEditModal(staffMember)}
                        className="p-2 text-purple-600 hover:bg-purple-50 rounded transition-colors cursor-pointer flex-shrink-0"
                        title="Edit Staff"
                      >
                        <Edit className="w-4 h-4" />
                      </button>

                      <button
                        onClick={() => openToggleConfirm(staffMember)}
                        className={`p-2 rounded transition-colors cursor-pointer flex-shrink-0 ${
                          staffMember.account_status === 'Active'
                            ? 'text-red-600 hover:bg-red-50'
                            : 'text-green-600 hover:bg-green-50'
                        }`}
                        title={staffMember.account_status === 'Active' ? 'Deactivate' : 'Activate'}
                      >
                        {staffMember.account_status === 'Active' ? (
                          <UserX className="w-4 h-4" />
                        ) : (
                          <UserCheck className="w-4 h-4" />
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

          <div className="flex justify-end space-x-3 pt-4">
            <button
              onClick={closeAddModal}
              className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              onClick={handleAddStaff}
              disabled={createForm.processing}
              className="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors cursor-pointer disabled:opacity-60"
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

          <div className="flex justify-end space-x-3 pt-4">
            <button
              onClick={closeEditModal}
              className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              onClick={handleEditStaff}
              disabled={editForm.processing}
              className="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors cursor-pointer disabled:opacity-60"
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