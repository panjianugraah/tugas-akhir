import axios from 'axios';

const API_URL = '/api/periode-ppdb';

export type PeriodePpdbPayload = {
    nama_periode: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    kuota: number | null;
};

/**
 * Ambil semua periode.
 * Opsional: { search: '2027' }
 */
export const getAllPeriodePpdb = async (params?: { search?: string }) => {
    const response = await axios.get(API_URL, { params });
    return response.data.data || response.data;
};

/**
 * Ambil detail periode.
 */
export const getPeriodePpdbById = async (id: number) => {
    const response = await axios.get(`${API_URL}/${id}`);
    return response.data.data || response.data;
};

/**
 * Tambah periode.
 */
export const createPeriodePpdb = async (data: PeriodePpdbPayload) => {
    const response = await axios.post(API_URL, data);
    return response.data;
};

/**
 * Edit periode.
 */
export const updatePeriodePpdb = async (
    id: number,
    data: PeriodePpdbPayload,
) => {
    const response = await axios.put(`${API_URL}/${id}`, data);
    return response.data;
};

/**
 * Hapus periode yang belum memiliki pendaftar.
 */
export const deletePeriodePpdb = async (id: number) => {
    const response = await axios.delete(`${API_URL}/${id}`);
    return response.data;
};

export const getPeriodePpdbAktif = async () => {
    const response = await axios.get(`${API_URL}/aktif`);
    return response.data.data || response.data;
};

/** Ambil periode aktif dan mendatang untuk ditampilkan di halaman publik. */
export const getJadwalPeriodePpdb = async () => {
    const response = await axios.get(`${API_URL}/jadwal`);
    return response.data.data || response.data;
};
