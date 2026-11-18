import axios from "axios";

const API_URL = "/api/ppdb";

/**
 * Ambil semua pendaftar PPDB
 * (opsional: { status: 'menunggu', search: 'budi' })
 */
export const getAllPpdb = async (params?: any) => {
    const response = await axios.get(API_URL, { params });
    return response.data.data || response.data;
};

/**
 * Ambil pendaftar by id
 */
export const getPpdbById = async (id: number) => {
    const response = await axios.get(`${API_URL}/${id}`);
    return response.data.data || response.data;
};

/**
 * Create pendaftar (dipakai di form PPDB)
 */
export const createPpdb = async (data: any) => {
    const response = await axios.post(API_URL, data);
    return response.data;
};

/**
 * Update pendaftar
 */
export const updatePpdb = async (id: number, data: any) => {
    const response = await axios.put(`${API_URL}/${id}`, data);
    return response.data;
};

/**
 * Update status pendaftar (menunggu / diterima / ditolak)
 */
export const updateStatusPpdb = async (id: number, status: string) => {
    const response = await axios.put(`${API_URL}/${id}`, { status });
    return response.data;
};

/**
 * Delete pendaftar
 */
export const deletePpdb = async (id: number) => {
    const response = await axios.delete(`${API_URL}/${id}`);
    return response.data;
};