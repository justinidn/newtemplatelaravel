<dialog class="dialog dialog-scrollable" id="departmentsModal" x-data="departmentModal()" @open-departments-modal.window="openAdd()" @edit-departments-modal.window="openEdit($event.detail)">
    <div class="dialog-header">
        <h1 class="dialog-title" x-text="isEdit ? 'Edit Department' : 'Tambah Department Baru'"></h1>
        <button type="button" class="btn-close" commandfor="departmentsModal" command="close" aria-label="Close"></button>
    </div>
    <div class="dialog-body">
        <form id="departmentsForm" @submit.prevent="submitForm()">
            <x-form.input name="department_name" label="Nama Department" x-model="form.department_name" required />
            <template x-if="errors.department_name">
                <div class="invalid-feedback d-block" x-text="errors.department_name"></div>
            </template>
            <x-form.input name="department_alias" label="Alias Department" x-model="form.department_alias" required />
            <template x-if="errors.department_alias">
                <div class="invalid-feedback d-block" x-text="errors.department_alias"></div>
            </template>
            <div class="mb-3" x-show="isEdit">
                <label class="form-label">Status</label>
                <div class="form-field">
                    <div class="switch" style="--bs-switch-width: 2.75rem; --bs-switch-indicator-width: 1.5rem;">
                        <input type="checkbox" value="1" id="switchActiveUser" role="switch" switch x-model="form.is_active">
                    </div>
                    <label for="switchActiveUser">Active</label>
                </div>
            </div>
        </form>
    </div>

    <div class="dialog-footer">
        <x-button-close commandfor="departmentsModal" command="close" />
        <button type="button" class="btn btn-subtle theme-primary" @click="submitForm()">Simpan</button>
    </div>
</dialog>

<style>
    #departmentsForm [x-model][aria-invalid="true"],
    #departmentsForm .is-invalid [x-model] {
        border-color: var(--bs-danger);
    }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('departmentModal', () => ({
            isEdit: false,
            originalForm: {},
            form: {
                id: '',
                department_name: '',
                is_active: true
            },
            errors: {},

            openAdd() {
                this.isEdit = false;
                this.form = {
                    id: '',
                    department_name: '',
                    department_alias: '',
                    is_active: true
                };
                this.originalForm = {};
                this.errors = {};
                this.clearValidationAttributes();
                document.getElementById('departmentsModal').showModal();
            },

            openEdit(item) {
                this.isEdit = true;
                this.form = {
                    id: item.id,
                    department_name: item.department_name,
                    department_alias: item.department_alias,
                    is_active: Boolean(Number(item.is_active))
                };
                this.originalForm = {
                    ...this.form
                };
                this.errors = {};
                this.clearValidationAttributes();
                document.getElementById('departmentsModal').showModal();
            },

            clearValidationAttributes() {
                this.$nextTick(() => {
                    document.querySelectorAll('#departmentsForm [x-model]').forEach(el => {
                        el.removeAttribute('aria-invalid');
                    });
                });
            },

            submitForm() {
                this.errors = {};
                this.clearValidationAttributes();

                const formData = {
                    ...this.form
                };

                const request = this.isEdit ?
                    axios.put(
                        "{{ route('departments.update', ['department' => '__ID__']) }}"
                        .replace('__ID__', this.form.id),
                        formData
                    ) :
                    axios.post("{{ route('departments.store') }}", formData);

                request
                    .then(response => {
                        document.getElementById('departmentsModal').close();
                        window.dispatchEvent(new CustomEvent('departments-saved'));
                        alert(response.data.message || 'Data berhasil disimpan.');
                    })
                    .catch(error => {
                        if (error.response) {
                            if (error.response.status === 422 && error.response.data.errors) {
                                const backendErrors = error.response.data.errors;
                                for (const field in backendErrors) {
                                    this.errors[field] = backendErrors[field][0];
                                }
                                this.$nextTick(() => {
                                    document.querySelectorAll('#departmentsForm [x-model]').forEach(el => {
                                        const modelAttr = el.getAttribute('x-model');
                                        const field = modelAttr ? modelAttr.split('.').pop() : null;
                                        if (field && this.errors[field]) {
                                            el.setAttribute('aria-invalid', 'true');
                                        } else {
                                            el.removeAttribute('aria-invalid');
                                        }
                                    });
                                });
                                alert('Validasi gagal. Periksa kembali isian Anda.');
                            } else if (error.response.status === 403) {
                                alert('Akses Ditolak: Anda tidak memiliki permission untuk melakukan aksi ini.');
                            } else {
                                alert('Kesalahan Server: ' + (error.response.data.message || 'Terjadi kesalahan pada server.'));
                            }
                        } else {
                            alert('Koneksi Gagal: Tidak dapat terhubung ke server.');
                        }
                    });
            }
        }));
    });
</script>