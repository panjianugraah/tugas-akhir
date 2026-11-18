import axios from "axios";

const API_URL = "/api/galeri";

/**
 * Ubah data menjadi FormData untuk upload gambar.
 */
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
 * Ambil semua foto galeri.
 * Opsional: { search: 'upacara' }
 */
export const getAllGaleri = async (params?: any) => {
    const response = await axios.get(API_URL, { params });
    return response.data.data || response.data;
};

/**
 * Ambil foto galeri by id.
 */
export const getGaleriById = async (id: number) => {
    const response = await axios.get(`${API_URL}/${id}`);
    return response.data.data || response.data;
};

/**
 * Tambah foto galeri.
 * data: { gambar: File, deskripsi: string }
 */
export const createGaleri = async (data: any) => {
    const response = await axios.post(API_URL, toFormData(data));
    return response.data;
};

/**
 * Edit deskripsi atau ganti gambar.
 * Gambar boleh tidak dikirim jika hanya mengubah deskripsi.
 */
export const updateGaleri = async (id: number, data: any) => {
    const response = await axios.post(
        `${API_URL}/${id}`,
        toFormData({ ...data, _method: "PUT" })
    );

    return response.data;
};

/**
 * Hapus foto galeri.
 */
export const deleteGaleri = async (id: number) => {
    const response = await axios.delete(`${API_URL}/${id}`);
    return response.data;
};