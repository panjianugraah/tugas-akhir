import axios from "axios";

const API_URL = "/api/repository";

const toFormData = (data: any) => {
    const form = new FormData();

    Object.entries(data).forEach(([key, value]) => {
        if (value === undefined) return;

        if (value instanceof File) {
            form.append(key, value);
        } else {
            form.append(key, value === null ? "" : String(value));
        }
    });

    return form;
};

/**
 * Ambil semua dokumen.
 */
export const getAllRepository = async (params?: any) => {
    const response = await axios.get(API_URL, { params });
    return response.data.data || response.data;
};

/**
 * Ambil dokumen by id.
 */
export const getRepositoryById = async (id: number) => {
    const response = await axios.get(`${API_URL}/${id}`);
    return response.data.data || response.data;
};

/**
 * Tambah dokumen dengan file.
 */
export const createRepository = async (data: any) => {
    const response = await axios.post(API_URL, toFormData(data));
    return response.data;
};

/**
 * Edit dokumen, boleh disertai file pengganti.
 */
export const updateRepository = async (id: number, data: any) => {
    const response = await axios.post(
        `${API_URL}/${id}`,
        toFormData({ ...data, _method: "PUT" })
    );

    return response.data;
};

/**
 * Hapus dokumen.
 */
export const deleteRepository = async (id: number) => {
    const response = await axios.delete(`${API_URL}/${id}`);
    return response.data;
};

/**
 * Daftar dokumen setelah akses diberikan.
 */
export const getRepositoryPublik = async () => {
    const response = await axios.get(`${API_URL}/publik`);
    return response.data.data || response.data;
};

/**
 * Masukkan kode akses.
 */
export const bukaAksesRepository = async (kode: string) => {
    const response = await axios.post(`${API_URL}/akses`, { kode });
    return response.data;
};

/**
 * Tutup akses pengunjung.
 */
export const tutupAksesRepository = async () => {
    const response = await axios.post(`${API_URL}/keluar`);
    return response.data;
};

/**
 * Atur kode dari dashboard.
 */
export const simpanKodeRepository = async (kode: string) => {
    const response = await axios.put(`${API_URL}/kode-akses`, { kode });
    return response.data;
};

export const resetAksesRepository = async () => {
    const response = await axios.post(`${API_URL}/reset-akses`);
    return response.data;
};
