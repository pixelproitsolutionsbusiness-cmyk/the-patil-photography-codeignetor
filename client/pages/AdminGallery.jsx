import React, { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Link } from "react-router-dom";
import { toast } from "sonner";
import { Plus, Pencil, Trash2, Image as ImageIcon, Filter } from "lucide-react";
import Skeleton from "../components/Skeleton";
import PageHeader from "../components/PageHeader";
import { getImageUrl } from "../lib/apiFetch";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

export default function AdminGallery() {
  const queryClient = useQueryClient();
  const [modalOpen, setModalOpen] = useState(false);
  const [form, setForm] = useState({ id: null, title: "", image: [], category: "General", status: "Active" });
  const [deleteId, setDeleteId] = useState(null);
  const [isCategoryModalOpen, setIsCategoryModalOpen] = useState(false);
  const [newCategory, setNewCategory] = useState("");

  const { data: galleryItems = [], isLoading } = useQuery({
    queryKey: ["gallery"],
    queryFn: async () => {
      const res = await fetch("/api/gallery");
      if (!res.ok) throw new Error("Failed to fetch gallery");
      const data = await res.json();
      // Ensure image is always an array for the UI
      return data.map(item => ({
        ...item,
        image: Array.isArray(item.image) ? item.image : (item.image ? [item.image] : [])
      }));
    },
  });

  const { data: eventTypes = [] } = useQuery({
    queryKey: ["eventTypes"],
    queryFn: async () => {
      const res = await fetch("/api/event-types");
      if (!res.ok) throw new Error("Failed to fetch event types");
      return res.json();
    },
  });

  const mutation = useMutation({
    mutationFn: async (data) => {
      const url = data.id ? `/api/gallery/${data.id}` : "/api/gallery";
      const method = data.id ? "PUT" : "POST";
      const { id, ...body } = data;
      const res = await fetch(url, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      });
      if (!res.ok) throw new Error("Failed to save gallery item");
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["gallery"] });
      toast.success(form.id ? "Gallery updated" : "Gallery created");
      setModalOpen(false);
      setForm({ id: null, title: "", image: [], category: "General", status: "Active" });
    },
    onError: (err) => toast.error(err.message),
  });

  const deleteMutation = useMutation({
    mutationFn: async (id) => {
      const res = await fetch(`/api/gallery/${id}`, { method: "DELETE" });
      if (!res.ok) throw new Error("Failed to delete item");
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["gallery"] });
      toast.success("Item deleted");
      setDeleteId(null);
    },
  });

  const handleImageUpload = async (e) => {
    const files = Array.from(e.target.files);
    if (files.length > 0) {
      const readers = files.map(file => {
        return new Promise((resolve) => {
          const reader = new FileReader();
          reader.onloadend = () => resolve(reader.result);
          reader.readAsDataURL(file);
        });
      });
      const results = await Promise.all(readers);
      setForm({ ...form, image: [...form.image, ...results] });
    }
  };

  const removeImage = (index) => {
    setForm({ ...form, image: form.image.filter((_, i) => i !== index) });
  };

  const handleSaveCategory = () => {
    if (newCategory.trim()) {
      const trimmed = newCategory.trim();
      
      // Try to save to backend
      fetch('/api/event-types', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: trimmed }),
      }).catch(err => console.error(err));

      queryClient.invalidateQueries({ queryKey: ["eventTypes"] });
      
      setForm(prev => ({ ...prev, category: trimmed }));
      setIsCategoryModalOpen(false);
      setNewCategory("");
    }
  };

  return (
    <div className="mt-0 px-0 pt-0 pb-6 container mx-auto animate-in fade-in duration-500">
      <PageHeader
        title="Gallery"
        description="Manage your gallery and portfolio images."
        action={
          <button
            onClick={() => { setForm({ id: null, title: "", image: [], category: "General", status: "Active" }); setModalOpen(true); }}
            className="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-gray-800 transition-all"
          >
            <Plus size={18} /> Add Gallery
          </button>
        }
      />

      {/* Gallery Grid */}
      {isLoading ? (
        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
          {[1, 2, 3, 4, 5, 6, 7, 8].map((_, index) => (
            <div key={index} className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
              <div className="relative aspect-square bg-gray-100">
                <Skeleton width="100%" height="100%" />
              </div>
              <div className="p-4">
                <Skeleton width="70%" height="24px" style={{ marginBottom: "8px" }} />
                <div className="flex justify-between items-center mt-2">
                  <Skeleton width="60px" height="20px" borderRadius="6px" />
                  <Skeleton width="12px" height="12px" borderRadius="50%" />
                </div>
              </div>
            </div>
          ))}
        </div>
      ) : galleryItems.length === 0 ? (
        <div className="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-200">
          <ImageIcon className="mx-auto text-gray-300 mb-4" size={48} />
          <p className="text-gray-500 font-medium">No images in gallery yet.</p>
          <button 
            onClick={() => setModalOpen(true)}
            className="mt-4 text-gold-600 font-semibold hover:underline"
          >
            Create your first masterpiece
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
          {galleryItems.map((item) => (
            <div key={item._id} className="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
              <div className="relative aspect-[4/3] bg-gray-100 flex items-center justify-center overflow-hidden">
                {item.image && item.image.length > 0 ? (
                  <img src={getImageUrl(item.image[0])} alt={item.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                ) : (
                  <ImageIcon className="text-gray-400" size={32} />
                )}
                
                {/* Image Count Badge */}
                {item.image && item.image.length > 1 && (
                  <div className="absolute top-3 left-3 bg-black/60 backdrop-blur-md text-white px-2 py-1 rounded-lg text-xs font-bold flex items-center gap-1.5 border border-white/20">
                    <ImageIcon size={12} />
                    {item.image.length} Photos
                  </div>
                )}

                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-between p-4">
                  <div className="flex gap-2">
                    <button
                      onClick={() => { setForm({ id: item._id, title: item.title, image: item.image, category: item.category, status: item.status }); setModalOpen(true); }}
                      className="p-2.5 bg-white text-gray-900 rounded-xl hover:scale-110 active:scale-95 transition-all shadow-lg"
                      title="Edit Gallery"
                    >
                      <Pencil size={16} />
                    </button>
                    <button
                      onClick={() => setDeleteId(item._id)}
                      className="p-2.5 bg-red-500 text-white rounded-xl hover:scale-110 active:scale-95 transition-all shadow-lg"
                      title="Delete Entry"
                    >
                      <Trash2 size={16} />
                    </button>
                  </div>
                </div>
              </div>
              <div className="p-5">
                <div className="flex justify-between items-start gap-2 mb-2">
                  <h3 className="font-bold text-gray-900 truncate flex-1 uppercase tracking-tight font-playfair">{item.title || "Untitled Moment"}</h3>
                  <span className={`w-2 h-2 mt-1.5 rounded-full ${item.status === "Active" ? "bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]" : "bg-gray-300"}`}></span>
                </div>
                <div className="flex justify-between items-center">
                  <span className="text-[10px] font-bold uppercase tracking-wider text-gold-600 bg-gold-50 px-2.5 py-1 rounded-full border border-gold-100">{item.category}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-40 p-4" onClick={(e) => {
          if (isCategoryModalOpen) {
            e.stopPropagation();
            return;
          }
          setModalOpen(false);
        }}>
          <div className="bg-white rounded-2xl p-6 w-full max-w-3xl shadow-2xl animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto custom-scrollbar" onClick={(e) => e.stopPropagation()}>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-bold text-gray-900">{form.id ? "Edit Gallery" : "New Gallery"}</h2>
              <button onClick={() => setModalOpen(false)} className="text-gray-400 hover:text-gray-600 p-1">✕</button>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div className="space-y-5">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1.5">Title</label>
                  <input
                    type="text"
                    value={form.title}
                    onChange={(e) => setForm({ ...form, title: e.target.value })}
                    className="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-black focus:border-transparent outline-none transition-shadow"
                    placeholder="e.g. Royal Wedding Bliss"
                  />
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-1.5">Category</label>
                    <div className="flex gap-2">
                      <Select
                        value={form.category}
                        onValueChange={(value) => setForm({ ...form, category: value })}
                      >
                        <SelectTrigger className="flex-1">
                          <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent className="z-[110]">
                          <SelectItem value="General">General</SelectItem>
                          {eventTypes.map((type) => (
                            <SelectItem key={type._id || type.name} value={type.name}>
                              {type.label || type.name}
                            </SelectItem>
                          ))}
                          {form.category && form.category !== "General" && !eventTypes.some(t => t.name === form.category) && (
                            <SelectItem key="legacy" value={form.category}>{form.category}</SelectItem>
                          )}
                        </SelectContent>
                      </Select>
                      <button
                        type="button"
                        onClick={() => setIsCategoryModalOpen(true)}
                        className="shrink-0 rounded-lg border border-gold-200 bg-gold-50 px-3 text-gold-600 hover:bg-gold-100 flex items-center justify-center"
                        title="Add new category"
                      >
                        <Plus size={16} />
                      </button>
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <Select
                      value={form.status}
                      onValueChange={(value) => setForm({ ...form, status: value })}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select status" />
                      </SelectTrigger>
                      <SelectContent className="z-[110]">
                        <SelectItem value="Active">Active</SelectItem>
                        <SelectItem value="Inactive">Inactive</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">
                  Gallery Images ({form.image.length})
                </label>
                
                <div className="grid grid-cols-3 gap-3 mb-4">
                  {form.image.map((img, index) => (
                    <div key={index} className="relative aspect-square rounded-lg overflow-hidden border border-gray-200 group shadow-sm">
                      <img src={getImageUrl(img)} alt={`Preview ${index}`} className="w-full h-full object-cover" />
                      <button
                        type="button"
                        onClick={() => removeImage(index)}
                        className="absolute top-1 right-1 bg-white text-red-500 p-1 rounded-full shadow-md hover:bg-red-50 transition-colors"
                      >
                        <Trash2 size={14} />
                      </button>
                    </div>
                  ))}
                  
                  <label className="aspect-square flex flex-col items-center justify-center cursor-pointer rounded-lg border-2 border-dashed border-gray-200 hover:bg-gray-50 transition-colors text-gray-400 group bg-gray-50/50">
                    <Plus size={24} className="mb-1" />
                    <span className="text-xs">Add</span>
                    <input
                      type="file"
                      multiple
                      accept="image/*"
                      onChange={handleImageUpload}
                      className="hidden"
                    />
                  </label>
                </div>
              </div>
            </div>

            <div className="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
              <button onClick={() => setModalOpen(false)} className="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
              <button 
                onClick={() => {
                  if(!form.title){
                    toast.error("Please provide a title");
                    return;
                  }
                  if(form.image.length === 0){
                    toast.error("Please upload at least one image");
                    return;
                  }
                  mutation.mutate(form)
                }} 
                disabled={mutation.isPending} 
                className="px-5 py-2.5 text-sm font-medium bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors shadow-md flex items-center gap-2"
              >
                {mutation.isPending ? (
                  <>
                    <span className="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin" />
                    Saving...
                  </>
                ) : (
                  form.id ? "Update Gallery" : "Add Gallery"
                )}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Delete Confirmation */}
      {deleteId && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-[110] p-4" onClick={() => setDeleteId(null)}>
          <div className="bg-white rounded-3xl p-8 w-full max-w-sm text-center shadow-2xl animate-in fade-in zoom-in duration-300" onClick={(e) => e.stopPropagation()}>
            <div className="w-16 h-16 bg-red-50 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-6 rotate-12">
              <Trash2 size={32} />
            </div>
            <h3 className="text-xl font-bold text-gray-900 mb-3 font-playfair">Remove Entry?</h3>
            <p className="text-gray-500 mb-8 text-sm leading-relaxed">This will permanently delete this gallery and all its associated images. This cannot be undone.</p>
            <div className="flex gap-4">
              <button onClick={() => setDeleteId(null)} className="flex-1 px-4 py-3.5 text-sm font-bold text-gray-500 hover:bg-gray-50 rounded-2xl transition-all border-2 border-transparent">Cancel</button>
              <button onClick={() => deleteMutation.mutate(deleteId)} className="flex-1 px-4 py-3.5 text-sm font-bold bg-red-500 text-white rounded-2xl hover:bg-red-600 transition-all shadow-lg shadow-red-200">Yes, Delete</button>
            </div>
          </div>
        </div>
      )}

      {/* Category Modal */}
      {isCategoryModalOpen && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4" onClick={() => setIsCategoryModalOpen(false)}>
          <div className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" onClick={(e) => e.stopPropagation()}>
            <h3 className="text-lg font-semibold text-gray-900">Add New Category</h3>
            <p className="mt-1 text-xs text-gray-500">Enter a new category to add to the list.</p>
            <div className="mt-4">
              <label className="block text-sm font-medium text-gray-700">Category Name</label>
              <input
                type="text"
                value={newCategory}
                onChange={(e) => setNewCategory(e.target.value)}
                className="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-black focus:border-transparent outline-none"
                placeholder="e.g. Corporate Event"
                autoFocus
              />
            </div>
            <div className="mt-6 flex justify-end gap-3">
              <button
                className="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50 font-medium"
                onClick={() => setIsCategoryModalOpen(false)}
              >
                Cancel
              </button>
              <button
                className="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                onClick={handleSaveCategory}
              >
                Add Category
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
