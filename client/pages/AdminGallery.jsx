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
  const [showSuccess, setShowSuccess] = useState(false);

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
      setShowSuccess(true);
      setTimeout(() => setShowSuccess(false), 3000);
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

  return (
    <div className="mt-0 px-0 pt-0 pb-6 container mx-auto animate-in fade-in duration-500">
      <PageHeader
        title="Gallery"
        description="Manage your gallery and portfolio images."
        action={
          <button
            onClick={() => { setForm({ id: null, title: "", image: [], category: "General", status: "Active" }); setModalOpen(true); }}
            className="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-gray-800 transition-all font-playfair"
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
        <div className="fixed inset-0 bg-black/70 backdrop-blur-md flex items-center justify-center z-40 p-4 font-sans" onClick={() => setModalOpen(false)}>
          <div className="bg-white rounded-3xl w-full max-w-4xl shadow-2xl animate-in fade-in zoom-in duration-300 max-h-[90vh] flex flex-col overflow-hidden" onClick={(e) => e.stopPropagation()}>
            <div className="flex justify-between items-center p-6 border-b border-gray-100">
              <div>
                <h2 className="text-2xl font-bold text-gray-900 font-playfair">{form.id ? "Edit Masterpiece" : "New Collection"}</h2>
                <p className="text-sm text-gray-500">Capture the essence of your photography work.</p>
              </div>
              <button onClick={() => setModalOpen(false)} className="text-gray-400 hover:text-gray-900 transition-colors p-2 bg-gray-50 rounded-full">✕</button>
            </div>

            <div className="flex-1 overflow-y-auto p-8 custom-scrollbar">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div className="space-y-6">
                  <div>
                    <label className="block text-xs font-bold uppercase tracking-[0.1em] text-gray-500 mb-2">Collection Title</label>
                    <input
                      type="text"
                      value={form.title}
                      onChange={(e) => setForm({ ...form, title: e.target.value })}
                      className="w-full border-2 border-gray-100 rounded-2xl px-5 py-3.5 text-base focus:ring-2 focus:ring-gold-500 focus:border-transparent outline-none transition-all placeholder:text-gray-300"
                      placeholder="e.g. Royal Wedding Bliss"
                    />
                  </div>
                  
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <div className="flex justify-between items-center mb-2">
                        <label className="block text-xs font-bold uppercase tracking-[0.1em] text-gray-500">Category</label>
                        <Link to="/admin-common-types" className="text-[10px] text-gold-600 hover:text-gold-700 font-bold uppercase tracking-wider hover:underline">Manage Categories</Link>
                      </div>
                      <Select
                        value={form.category}
                        onValueChange={(value) => setForm({ ...form, category: value })}
                      >
                        <SelectTrigger className="rounded-2xl border-2 border-gray-100 py-6">
                          <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent className="rounded-xl border-gray-100 shadow-xl">
                          <SelectItem value="General">General</SelectItem>
                          {eventTypes.map((type) => (
                            <SelectItem key={type._id} value={type.name}>
                              {type.label || type.name}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>
                    <div>
                      <label className="block text-xs font-bold uppercase tracking-[0.1em] text-gray-500 mb-2">Visibility</label>
                      <Select
                        value={form.status}
                        onValueChange={(value) => setForm({ ...form, status: value })}
                      >
                        <SelectTrigger className="rounded-2xl border-2 border-gray-100 py-6">
                          <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent className="rounded-xl border-gray-100 shadow-xl">
                          <SelectItem value="Active">Public</SelectItem>
                          <SelectItem value="Inactive">Hidden</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold uppercase tracking-[0.1em] text-gray-500 mb-2">
                    Gallery Images ({form.image.length})
                  </label>
                  
                  <div className="grid grid-cols-3 gap-3 mb-4">
                    {form.image.map((img, index) => (
                      <div key={index} className="relative aspect-square rounded-2xl overflow-hidden border-2 border-gray-100 group shadow-sm">
                        <img src={getImageUrl(img)} alt={`Preview ${index}`} className="w-full h-full object-cover" />
                        <button
                          type="button"
                          onClick={() => removeImage(index)}
                          className="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                          ✕
                        </button>
                      </div>
                    ))}
                    
                    <label className="aspect-square flex flex-col items-center justify-center cursor-pointer rounded-2xl border-2 border-dashed border-gray-200 hover:border-gold-500 hover:bg-gold-50/30 transition-all text-gray-400 group">
                      <Plus size={24} className="group-hover:scale-110 transition-transform mb-1" />
                      <span className="text-[10px] font-bold uppercase tracking-tighter">Add</span>
                      <input
                        type="file"
                        multiple
                        accept="image/*"
                        onChange={handleImageUpload}
                        className="hidden"
                      />
                    </label>
                  </div>
                  
                  <div className="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                    <p className="text-[10px] text-gray-400 font-bold uppercase mb-2">Quick Tips</p>
                    <ul className="text-xs text-gray-500 space-y-1.5 list-disc list-inside">
                      <li>Upload multiple images at once</li>
                      <li>High resolution JPEG/PNG recommended</li>
                      <li>First image will be used as the cover</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div className="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-4">
              <button onClick={() => setModalOpen(false)} className="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors uppercase tracking-widest">Discard</button>
              <button 
                onClick={() => mutation.mutate(form)} 
                disabled={mutation.isPending} 
                className="px-8 py-3.5 bg-gray-900 text-white rounded-2xl hover:bg-black transition-all shadow-xl disabled:opacity-50 flex items-center gap-3 font-bold uppercase tracking-widest text-xs"
              >
                {mutation.isPending ? (
                  <>
                    <span className="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin" />
                    Processing...
                  </>
                ) : (
                  form.id ? "Apply Changes" : "Publish Gallery"
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

      {/* Success Popup */}
      {showSuccess && (
        <div className="fixed bottom-10 left-1/2 -translate-x-1/2 z-[150] animate-in slide-in-from-bottom duration-500">
          <div className="flex items-center gap-4 rounded-3xl bg-gray-900 px-8 py-4 shadow-2xl text-white border border-white/10">
            <div className="w-8 h-8 bg-gold-500 rounded-full flex items-center justify-center text-lg">✨</div>
            <div className="font-bold text-sm uppercase tracking-widest">Gallery synchronized successfully</div>
          </div>
        </div>
      )}
    </div>
  );
}
