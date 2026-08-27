"use client";

import {
  FormEvent,
  useCallback,
  useEffect,
  useMemo,
  useState,
} from "react";

import {
  Pencil,
  Plus,
  Trash2,
  X,
} from "lucide-react";

import { apiFetch } from "@/lib/api";

type Category = {
  id: number;
  parent_id?: number | null;
  name: string;
  slug: string;
  description?: string | null;
  image?: string | null;
  status: string;
  sort_order: number;
  parent?: {
    id: number;
    name: string;
  } | null;
  children_count?: number;
};

type CategoryFormData = {
  name: string;
  description: string;
  status: string;
  sort_order: string;
  parent_id: string;
  image:File | null;
};

export default function CategoriesPage() {
  const [categories, setCategories] = useState<Category[]>([]);

  const [loading, setLoading] = useState(true);

  const [showForm, setShowForm] = useState(false);

  const [editing, setEditing] =
    useState<Category | null>(null);

  const [name, setName] =
    useState("");

  const [description, setDescription] =
    useState("");

  const [status, setStatus] =
    useState("active");

  const [sortOrder, setSortOrder] =
    useState("0");

  const [parentId, setParentId] =
    useState("");
  const [image,setImage] = useState<File | null>(null);

  const [imagePreview,setImagePreview] = useState("");

  const [saving, setSaving] =
    useState(false);

  const [error, setError] =
    useState("");

  /*
  |--------------------------------------------------------------------------
  | Fetch Categories
  |--------------------------------------------------------------------------
  */

  const fetchCategories =
    useCallback(async () => {
      try {
        setLoading(true);
        setError("");

        const response =
          await apiFetch(
            "/admin/categories"
          );

        const data =
          await response.json();

        if (!response.ok) {
          throw new Error(
            data?.message ||
              "Unable to load categories."
          );
        }

        /*
        |--------------------------------------------------------------------------
        | Support different Laravel response structures
        |--------------------------------------------------------------------------
        */

        let categoryData: Category[] = [];

        if (
          Array.isArray(data?.data)
        ) {
          categoryData = data.data;
        } else if (
          Array.isArray(data?.data?.data)
        ) {
          categoryData =
            data.data.data;
        } else if (
          Array.isArray(data)
        ) {
          categoryData = data;
        }

        setCategories(
          categoryData
        );
      } catch (exception) {
        setError(
          exception instanceof Error
            ? exception.message
            : "Unable to load categories."
        );
      } finally {
        setLoading(false);
      }
    }, []);

  /*
  |--------------------------------------------------------------------------
  | Initial Load
  |--------------------------------------------------------------------------
  */

  useEffect(() => {
    void fetchCategories();
  }, [fetchCategories]);

  /*
  |--------------------------------------------------------------------------
  | Parent Categories
  |--------------------------------------------------------------------------
  |
  | Only top-level categories can become parents.
  |
  | We intentionally support:
  | null
  | undefined
  | 0
  |
  | because different Laravel/API responses may represent
  | a missing parent differently.
  |
  */

  const parentOptions =
    useMemo(() => {
      return categories
        .filter((category) => {
          const isTopLevel =
            category.parent_id === null ||
            category.parent_id === undefined ||
            category.parent_id === 0;

          const isNotCurrentCategory =
            category.id !== editing?.id;

          return (
            isTopLevel &&
            isNotCurrentCategory
          );
        })
        .sort(
          (a, b) =>
            Number(a.sort_order || 0) -
              Number(b.sort_order || 0) ||
            a.name.localeCompare(
              b.name
            )
        );
    }, [categories, editing]);
    /* Find parent category from parent_id */

    const categoryMap = useMemo(() => {
  return new Map(
    categories.map((category) => [
      Number(category.id),
      category,
    ])
  );
}, [categories]);

function getParentName(category: Category) {
  if (
    category.parent_id === null ||
    category.parent_id === undefined ||
    category.parent_id === 0
  ) {
    return "Top level";
  }

  return (
    categoryMap.get(Number(category.parent_id))?.name ||
    "Top level"
  );
}

  /*
  |--------------------------------------------------------------------------
  | Reset Form
  |--------------------------------------------------------------------------
  */

  function resetForm() {
    setName("");
    setDescription("");
    setStatus("active");
    setSortOrder("0");
    setParentId("");
    setEditing(null);
    setImage(null);
    setImagePreview("");
    setError("");
  }

  /*
  |--------------------------------------------------------------------------
  | Create
  |--------------------------------------------------------------------------
  */

  function openCreate() {
    resetForm();
    setShowForm(true);
  }

  /*
  |--------------------------------------------------------------------------
  | Edit
  |--------------------------------------------------------------------------
  */

  function openEdit(
    category: Category
  ) {
    setEditing(category);

    setName(
      category.name || ""
    );

    setDescription(
      category.description || ""
    );

    setStatus(
      category.status || "active"
    );

    setSortOrder(
      String(
        category.sort_order ?? 0
      )
    );

    /*
    |--------------------------------------------------------------------------
    | Existing parent
    |--------------------------------------------------------------------------
    */

    setParentId(
      category.parent_id !==
        null &&
      category.parent_id !==
        undefined &&
      category.parent_id !== 0
        ? String(
            category.parent_id
          )
        : ""
    );
    if(category.image){

      setImagePreview(
      `${process.env.NEXT_PUBLIC_BACKEND_URL}/storage/${category.image}`
      );

      }else{

      setImagePreview("");

      }

    setError("");
    setShowForm(true);
  }

  /*
  |--------------------------------------------------------------------------
  | Close
  |--------------------------------------------------------------------------
  */

  function closeForm() {
    setShowForm(false);
    resetForm();
  }

  /*
  |--------------------------------------------------------------------------
  | Submit
  |--------------------------------------------------------------------------
  */

  async function handleSubmit(
  event: FormEvent
) {

  event.preventDefault();


  if (!name.trim()) {

    setError(
      "Category name is required."
    );

    return;

  }



  if (
    editing &&
    parentId &&
    Number(parentId) === editing.id
  ) {

    setError(
      "A category cannot be its own parent."
    );

    return;

  }



  setSaving(true);
  setError("");



  try {


    const endpoint =
      editing
        ? `/admin/categories/${editing.id}`
        : "/admin/categories";



    const formData = new FormData();



    formData.append(
      "name",
      name.trim()
    );



    formData.append(
      "description",
      description.trim() || ""
    );



    formData.append(
      "status",
      status
    );



    formData.append(
      "sort_order",
      String(
        Number(sortOrder) || 0
      )
    );



    if(parentId){

      formData.append(
        "parent_id",
        parentId
      );

    }



    if(image){

      formData.append(
        "image",
        image
      );

    }



    /*
    Laravel PUT fix
    Multipart PUT does not work properly
    */

    if(editing){

      formData.append(
        "_method",
        "PUT"
      );

    }



    const response =
      await apiFetch(
        endpoint,
        {

          method:"POST",

          body:formData,

        }
      );



    const data =
      await response.json();



    console.log(
      "CATEGORY RESPONSE:",
      data
    );



    if(!response.ok){


      const validationErrors =
        data?.errors as
        | Record<string,string[]>
        | undefined;



      const firstError =
        validationErrors
        ?
        Object.keys(validationErrors)[0]
        :
        undefined;



      throw new Error(

        data?.message ||

        (
          firstError
          ?
          validationErrors?.[firstError]?.[0]
          :
          undefined
        ) ||

        "Unable to save category."

      );

    }




    closeForm();


    await fetchCategories();



    window.dispatchEvent(
      new Event(
        "banglesmart:catalog-refresh"
      )
    );



  }
  catch(exception){


    setError(

      exception instanceof Error
      ?
      exception.message
      :
      "Unable to save category."

    );


  }
  finally{

    setSaving(false);

  }


}

  /*
  |--------------------------------------------------------------------------
  | Delete
  |--------------------------------------------------------------------------
  */

  async function deleteCategory(
    category: Category
  ) {
    const confirmed =
      window.confirm(
        `Delete "${category.name}"?\n\nChild categories will become top-level categories.`
      );

    if (!confirmed) {
      return;
    }

    try {
      setError("");

      const response =
        await apiFetch(
          `/admin/categories/${category.id}`,
          {
            method: "DELETE",
          }
        );

      const data =
        await response
          .json()
          .catch(
            () => null
          );

      if (!response.ok) {
        setError(
          data?.message ||
            "Unable to delete category."
        );

        return;
      }

      await fetchCategories();

      window.dispatchEvent(
        new Event(
          "banglesmart:catalog-refresh"
        )
      );
    } catch (exception) {
      setError(
        exception instanceof Error
          ? exception.message
          : "Unable to delete category."
      );
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Check Top Level
  |--------------------------------------------------------------------------
  */

  function isTopLevel(
    category: Category
  ) {
    return (
      category.parent_id ===
        null ||
      category.parent_id ===
        undefined ||
      category.parent_id ===
        0
    );
  }

  /*
  |--------------------------------------------------------------------------
  | Render
  |--------------------------------------------------------------------------
  */

  return (
    <div className="min-h-full">

      {/* -------------------------------------------------------------- */}
      {/* HEADER */}
      {/* -------------------------------------------------------------- */}

      <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>

          <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#c9a227]">
            Store taxonomy
          </p>

          <h1 className="mt-2 text-3xl font-semibold text-gray-900">
            Categories
          </h1>

          <p className="mt-1 text-sm text-gray-500">
            Create parent categories and
            subcategories for the storefront
            mega menu.
          </p>

        </div>

        <button
          type="button"
          onClick={openCreate}
          className="
            inline-flex
            items-center
            justify-center
            gap-2
            rounded-xl
            bg-gray-900
            px-5
            py-3
            text-sm
            font-semibold
            text-white
            transition
            hover:bg-black
          "
        >
          <Plus size={18} />

          Add category
        </button>

      </div>

      {/* -------------------------------------------------------------- */}
      {/* ERROR */}
      {/* -------------------------------------------------------------- */}

      {error && !showForm && (
        <div className="
          mb-5
          rounded-xl
          border
          border-red-200
          bg-red-50
          px-4
          py-3
          text-sm
          text-red-700
        ">
          {error}
        </div>
      )}

      {/* -------------------------------------------------------------- */}
      {/* CATEGORY TABLE */}
      {/* -------------------------------------------------------------- */}

      <div className="
        overflow-hidden
        rounded-2xl
        border
        border-gray-200
        bg-white
        shadow-sm
      ">

        <div className="overflow-x-auto">

          <table className="w-full min-w-[800px]">

            <thead className="
              border-b
              border-gray-200
              bg-gray-50
            ">

              <tr>

                <th className="
                  px-5
                  py-3
                  text-left
                  text-xs
                  font-medium
                  uppercase
                  tracking-wide
                  text-gray-500
                ">
                  Category
                </th>

                <th className="
                  px-5
                  py-3
                  text-left
                  text-xs
                  font-medium
                  uppercase
                  tracking-wide
                  text-gray-500
                ">
                  Parent
                </th>

                <th className="
                  px-5
                  py-3
                  text-left
                  text-xs
                  font-medium
                  uppercase
                  tracking-wide
                  text-gray-500
                ">
                  Status
                </th>

                <th className="
                  px-5
                  py-3
                  text-left
                  text-xs
                  font-medium
                  uppercase
                  tracking-wide
                  text-gray-500
                ">
                  Order
                </th>

                <th className="
                  px-5
                  py-3
                  text-right
                  text-xs
                  font-medium
                  uppercase
                  tracking-wide
                  text-gray-500
                ">
                  Actions
                </th>

              </tr>

            </thead>

            <tbody className="divide-y divide-gray-100">

              {loading ? (

                <tr>

                  <td
                    colSpan={5}
                    className="
                      px-5
                      py-12
                      text-center
                      text-sm
                      text-gray-500
                    "
                  >
                    Loading categories...
                  </td>

                </tr>

              ) : categories.length === 0 ? (

                <tr>

                  <td
                    colSpan={5}
                    className="
                      px-5
                      py-12
                      text-center
                      text-sm
                      text-gray-500
                    "
                  >
                    No categories yet.
                  </td>

                </tr>

              ) : (

                categories
                  .slice()
                  .sort(
                    (a, b) => {

                      /*
                      |--------------------------------------------------------------------------
                      | Parents first
                      |--------------------------------------------------------------------------
                      */

                      const aParent =
                        isTopLevel(a);

                      const bParent =
                        isTopLevel(b);

                      if (
                        aParent &&
                        !bParent
                      ) {
                        return -1;
                      }

                      if (
                        !aParent &&
                        bParent
                      ) {
                        return 1;
                      }

                      /*
                      |--------------------------------------------------------------------------
                      | Sort order
                      |--------------------------------------------------------------------------
                      */

                      return (
                        Number(
                          a.sort_order ||
                            0
                        ) -
                          Number(
                            b.sort_order ||
                              0
                          ) ||
                        a.name.localeCompare(
                          b.name
                        )
                      );
                    }
                  )
                  .map(
                    (category) => (

                      <tr
                        key={
                          category.id
                        }
                        className="
                          transition
                          hover:bg-gray-50
                        "
                      >

                        {/* CATEGORY */}

                        <td className="px-5 py-4">

                          <div
                            className={`
                              flex
                              items-start
                              gap-3
                              ${
                                !isTopLevel(
                                  category
                                )
                                  ? "pl-7"
                                  : ""
                              }
                            `}
                          >

                            {!isTopLevel(
                              category
                            ) ? (

                              <span className="
                                mt-1
                                text-sm
                                text-gray-300
                              ">
                                ↳
                              </span>

                            ) : (

                              <span className="
                                mt-2
                                h-2
                                w-2
                                shrink-0
                                rounded-full
                                bg-[#c9a227]
                              " />

                            )}

                            <div className="min-w-0">

                              <p
                                className={`
                                  ${
                                    isTopLevel(
                                      category
                                    )
                                      ? "font-semibold"
                                      : "font-medium"
                                  }
                                  text-gray-900
                                `}
                              >
                                {category.name}
                              </p>

                              {category.description && (
                                <p className="
                                  mt-1
                                  max-w-md
                                  truncate
                                  text-xs
                                  text-gray-500
                                ">
                                  {
                                    category.description
                                  }
                                </p>
                              )}

                            </div>

                          </div>

                        </td>

                        {/* PARENT */}

                        <td className="
                          px-5
                          py-4
                          text-sm
                          text-gray-500
                        ">

                          {getParentName(category)}

                        </td>

                        {/* STATUS */}

                        <td className="px-5 py-4">

                          <span
                            className={`
                              rounded-full
                              px-2.5
                              py-1
                              text-xs
                              font-medium
                              ${
                                category.status ===
                                "active"
                                  ? "bg-green-50 text-green-700"
                                  : "bg-gray-100 text-gray-600"
                              }
                            `}
                          >
                            {
                              category.status
                            }
                          </span>

                        </td>

                        {/* SORT */}

                        <td className="
                          px-5
                          py-4
                          text-sm
                          text-gray-600
                        ">
                          {
                            category.sort_order
                          }
                        </td>

                        {/* ACTIONS */}

                        <td className="px-5 py-4">

                          <div className="
                            flex
                            justify-end
                            gap-2
                          ">

                            <button
                              type="button"
                              onClick={() =>
                                openEdit(
                                  category
                                )
                              }
                              className="
                                rounded-lg
                                border
                                border-gray-200
                                p-2
                                transition
                                hover:bg-gray-100
                              "
                              title="Edit category"
                            >
                              <Pencil
                                size={16}
                              />
                            </button>

                            <button
                              type="button"
                              onClick={() =>
                                void deleteCategory(
                                  category
                                )
                              }
                              className="
                                rounded-lg
                                border
                                border-gray-200
                                p-2
                                text-red-600
                                transition
                                hover:bg-red-50
                              "
                              title="Delete category"
                            >
                              <Trash2
                                size={16}
                              />
                            </button>

                          </div>

                        </td>

                      </tr>

                    )
                  )

              )}

            </tbody>

          </table>

        </div>

      </div>

      {/* -------------------------------------------------------------- */}
      {/* ADD / EDIT DRAWER */}
      {/* -------------------------------------------------------------- */}

      {showForm && (

        <div className="
          fixed
          inset-0
          z-50
          flex
          justify-end
          bg-black/30
          backdrop-blur-[2px]
        ">

          <div className="
            h-full
            w-full
            max-w-lg
            overflow-y-auto
            bg-white
            shadow-2xl
          ">

            {/* DRAWER HEADER */}

            <div className="
              sticky
              top-0
              z-10
              flex
              items-center
              justify-between
              border-b
              border-gray-200
              bg-white
              px-6
              py-5
            ">

              <div>

                <p className="
                  text-[10px]
                  font-semibold
                  uppercase
                  tracking-[0.2em]
                  text-[#c9a227]
                ">
                  Store navigation
                </p>

                <h2 className="
                  mt-1
                  text-lg
                  font-semibold
                  text-gray-900
                ">
                  {editing
                    ? "Edit category"
                    : "Add category"}
                </h2>

              </div>

              <button
                type="button"
                onClick={closeForm}
                className="
                  rounded-lg
                  p-2
                  transition
                  hover:bg-gray-100
                "
              >
                <X size={20} />
              </button>

            </div>

            {/* FORM */}

            <form
              onSubmit={handleSubmit}
              className="space-y-5 p-6"
            >

              {error && (
                <div className="
                  rounded-xl
                  border
                  border-red-200
                  bg-red-50
                  p-3
                  text-sm
                  text-red-700
                ">
                  {error}
                </div>
              )}

              {/* CATEGORY NAME */}

              <div>

                <label className="
                  mb-2
                  block
                  text-sm
                  font-medium
                  text-gray-900
                ">
                  Category name
                </label>

                <input
                  required
                  value={name}
                  onChange={(event) =>
                    setName(
                      event.target.value
                    )
                  }
                  className="
                    w-full
                    rounded-xl
                    border
                    border-gray-200
                    px-4
                    py-3
                    text-sm
                    outline-none
                    transition
                    focus:border-[#c9a227]
                    focus:ring-2
                    focus:ring-[#c9a227]/10
                  "
                  placeholder="e.g. Glass Bangles"
                />

              </div>

              {/* CATEGORY IMAGE */}

                <div>

                <label
                className="
                mb-2
                block
                text-sm
                font-medium
                text-gray-900
                "
                >
                Category Image
                </label>


                <input

                type="file"

                accept="image/*"

                onChange={(event)=>{

                const file =
                event.target.files?.[0];

                if(file){

                setImage(file);

                setImagePreview(
                URL.createObjectURL(file)
                );

                }

                }}

                className="
                w-full
                rounded-xl
                border
                border-gray-200
                px-4
                py-3
                text-sm
                "

                />


                {
                imagePreview && (

                <div className="mt-4">

                <img

                src={imagePreview}

                alt="preview"

                className="
                h-32
                w-32
                rounded-xl
                object-cover
                border
                "

                />

                </div>

                )

                }


                </div>

              {/* PARENT CATEGORY */}

              <div>

                <label className="
                  mb-2
                  block
                  text-sm
                  font-medium
                  text-gray-900
                ">
                  Parent category
                </label>

                <select
                  value={parentId}
                  onChange={(event) =>
                    setParentId(
                      event.target.value
                    )
                  }
                  className="
                    w-full
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    px-4
                    py-3
                    text-sm
                    outline-none
                    transition
                    focus:border-[#c9a227]
                    focus:ring-2
                    focus:ring-[#c9a227]/10
                  "
                >

                  <option value="">
                    Top-level category
                  </option>

                  {parentOptions.length >
                  0 ? (

                    parentOptions.map(
                      (option) => (

                        <option
                          key={
                            option.id
                          }
                          value={
                            option.id
                          }
                        >
                          {option.name}
                        </option>

                      )
                    )

                  ) : null}

                </select>

                {parentOptions.length ===
                  0 && (
                  <p className="
                    mt-2
                    rounded-lg
                    bg-amber-50
                    px-3
                    py-2
                    text-xs
                    text-amber-700
                  ">
                    No top-level categories
                    are available yet. Create
                    a parent category first.
                  </p>
                )}

                {parentOptions.length >
                  0 && (
                  <p className="
                    mt-2
                    text-xs
                    text-gray-500
                  ">
                    Select a parent category
                    to make this a subcategory
                    in the mega menu.
                  </p>
                )}

              </div>

              {/* DESCRIPTION */}

              <div>

                <label className="
                  mb-2
                  block
                  text-sm
                  font-medium
                  text-gray-900
                ">
                  Description
                </label>

                <textarea
                  value={description}
                  onChange={(event) =>
                    setDescription(
                      event.target.value
                    )
                  }
                  rows={4}
                  className="
                    w-full
                    resize-none
                    rounded-xl
                    border
                    border-gray-200
                    px-4
                    py-3
                    text-sm
                    outline-none
                    transition
                    focus:border-[#c9a227]
                    focus:ring-2
                    focus:ring-[#c9a227]/10
                  "
                  placeholder="Optional category description"
                />

              </div>

              {/* STATUS + SORT */}

              <div className="
                grid
                gap-4
                sm:grid-cols-2
              ">

                <div>

                  <label className="
                    mb-2
                    block
                    text-sm
                    font-medium
                  ">
                    Status
                  </label>

                  <select
                    value={status}
                    onChange={(event) =>
                      setStatus(
                        event.target.value
                      )
                    }
                    className="
                      w-full
                      rounded-xl
                      border
                      border-gray-200
                      bg-white
                      px-4
                      py-3
                      text-sm
                    "
                  >

                    <option value="active">
                      Active
                    </option>

                    <option value="inactive">
                      Inactive
                    </option>

                  </select>

                </div>

                <div>

                  <label className="
                    mb-2
                    block
                    text-sm
                    font-medium
                  ">
                    Sort order
                  </label>

                  <input
                    type="number"
                    min="0"
                    value={sortOrder}
                    onChange={(event) =>
                      setSortOrder(
                        event.target.value
                      )
                    }
                    className="
                      w-full
                      rounded-xl
                      border
                      border-gray-200
                      px-4
                      py-3
                      text-sm
                    "
                  />

                </div>

              </div>

              {/* FORM BUTTONS */}

              <div className="
                flex
                gap-3
                border-t
                border-gray-100
                pt-5
              ">

                <button
                  type="button"
                  onClick={closeForm}
                  className="
                    flex-1
                    rounded-xl
                    border
                    border-gray-200
                    py-3
                    text-sm
                    font-semibold
                    text-gray-700
                    transition
                    hover:bg-gray-50
                  "
                >
                  Cancel
                </button>

                <button
                  disabled={saving}
                  type="submit"
                  className="
                    flex-1
                    rounded-xl
                    bg-gray-900
                    py-3
                    text-sm
                    font-semibold
                    text-white
                    transition
                    hover:bg-black
                    disabled:cursor-not-allowed
                    disabled:opacity-50
                  "
                >
                  {saving
                    ? "Saving..."
                    : editing
                    ? "Save changes"
                    : "Create category"}
                </button>

              </div>

            </form>

          </div>

        </div>

      )}

    </div>
  );
}