import axios from "axios";

const API_URL = "/api/mata-pelajaran";

export const getAllMataPelajaran = async () => {
    const response = await axios.get(API_URL);
    return response.data.data || response.data;
};

export const createMataPelajaran = async (data: any) => {
    const response = await axios.post(API_URL, data);
    return response.data;
};

export const updateMataPelajaran = async (id: number, data: any) => {
    const response = await axios.put(`${API_URL}/${id}`, data);
    return response.data;
};

export const deleteMataPelajaran = async (id: number) => {
    const response = await axios.delete(`${API_URL}/${id}`);
    return response.data;
};